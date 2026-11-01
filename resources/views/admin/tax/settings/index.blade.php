@extends('layouts.admin')

@section('title', 'Tax & VAT Settings')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 tax-settings-wrapper">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2">
                <i class="fas fa-sliders-h text-primary"></i> Tax &amp; VAT Settings
            </h4>
            <p class="text-secondary small mb-0">গ্লোবাল ভ্যাট অ্যাক্টিভেশন, ডিফল্ট রেট, অগ্রাধিকার ক্রম এবং পিওএস কনফিগারেশন।</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.tax.rules.index') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                <i class="fas fa-balance-scale"></i> <span>Tax Rules</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border border-success-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-check-circle me-2 fs-6"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-exclamation-circle me-2 fs-6"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <span class="fw-semibold small">অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</span>
            </div>
            <ul class="mb-0 ps-3 small text-secondary">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Priority Hierarchy Explainer Card -->
    <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-info-circle text-primary fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">ভ্যাট নির্ধারণের অগ্রাধিকার নিয়ম (Tax Calculation Hierarchy)</h6>
            </div>
            <div class="row g-2 text-dark small" style="font-size: 11.5px;">
                <div class="col-12 col-md-4">
                    <div class="p-2 bg-white rounded-1 border border-primary border-opacity-10 h-100">
                        <strong class="text-primary d-block mb-1">১. গ্লোবাল মাস্টার সুইচ</strong>
                        সুইচটি বন্ধ (Disabled) থাকলে ওয়েবসাইট, পিওএস বা ম্যানুয়াল—কোনো অর্ডারে কোনো ভ্যাট কার্যকর হবে না (সব ০%)।
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-2 bg-white rounded-1 border border-primary border-opacity-10 h-100">
                        <strong class="text-primary d-block mb-1">২. প্রোডাক্ট বা ক্যাটাগরি ভ্যাট</strong>
                        প্রোডাক্টে নির্দিষ্ট ভ্যাট (যেমন: ০% বা ২%) দেওয়া থাকলে গ্লোবাল রেট ওভাররাইড হয়ে প্রোডাক্টের ভ্যাটই কার্যকর হবে।
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-2 bg-white rounded-1 border border-primary border-opacity-10 h-100">
                        <strong class="text-primary d-block mb-1">৩. ডিফল্ট রেট (Fallback)</strong>
                        প্রোডাক্ট বা ক্যাটাগরিতে কোনো নির্দিষ্ট ভ্যাট সেট করা না থাকলে তবেই কেবল এই ডিফল্ট রেট (যেমন: ৫%) প্রযোজ্য হবে।
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('admin.tax.settings.update') }}" method="POST" id="taxSettingsForm">
        @csrf

        <div class="row g-2 g-md-3">
            <!-- Left Column: Primary VAT Configurations -->
            <div class="col-12 col-lg-7">
                <!-- VAT Activation & Rates Card -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-calculator text-primary"></i> General VAT &amp; Rates
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">
                            <!-- Toggle Master Switch -->
                            <div class="col-12">
                                <div class="p-2 border rounded-1" id="vatSwitchCard" style="background: {{ !empty($settings['vat_enabled']) ? '#f0fdf4' : '#fff1f2' }}; border-color: {{ !empty($settings['vat_enabled']) ? '#bbf7d0' : '#fecdd3' }} !important; transition: all 0.2s ease;">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input ms-0 mt-0 flex-shrink-0" type="checkbox" name="vat_enabled" value="1" id="vatEnabledSwitch" {{ !empty($settings['vat_enabled']) ? 'checked' : '' }} onchange="handleVatSwitchToggle(this)">
                                        <div class="flex-grow-1">
                                            <label class="form-check-label fw-bold text-dark small d-block" for="vatEnabledSwitch" id="vatSwitchLabel">
                                                {{ !empty($settings['vat_enabled']) ? 'VAT System: Active (সক্রিয়)' : 'VAT System: Disabled (নিষ্ক্রিয়)' }}
                                            </label>
                                            <span class="text-secondary d-block" id="vatSwitchHelp" style="font-size: 11px;">
                                                {{ !empty($settings['vat_enabled']) ? 'ওয়েবসাইট, পিওএস ও অর্ডারে স্বয়ংক্রিয়ভাবে ভ্যাট হিসাব করা হচ্ছে।' : 'পুরো সিস্টেমের ভ্যাট বন্ধ রয়েছে (কোনো ভ্যাট ধার্য হবে না)।' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- VAT Label -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">VAT Title / Label <span class="text-danger">*</span></label>
                                <input type="text" name="vat_label" class="form-control form-control-sm" value="{{ old('vat_label', $settings['vat_label'] ?? 'VAT') }}" placeholder="e.g. VAT or Govt. VAT" required>
                                <span class="text-secondary d-block mt-1" style="font-size: 11px;">ইনভয়েস ও রিসিটে এই নামটি প্রদর্শিত হবে।</span>
                            </div>

                            <!-- Default VAT Rate -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Default Rate (%) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" max="100" name="default_vat_rate" class="form-control font-monospace" value="{{ old('default_vat_rate', $settings['default_vat_rate'] ?? 5.00) }}" required>
                                    <span class="input-group-text bg-light text-secondary">%</span>
                                </div>
                                <span class="text-secondary d-block mt-1" style="font-size: 11px;">প্রোডাক্টে নির্দিষ্ট রেট না থাকলে এটি ব্যবহৃত হবে।</span>
                            </div>

                            <!-- Calculation Mode -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Calculation Method <span class="text-danger">*</span></label>
                                <select name="default_tax_calculation_type" class="form-select form-select-sm" required>
                                    <option value="exclusive" {{ (old('default_tax_calculation_type', $settings['default_tax_calculation_type'] ?? '') === 'exclusive') ? 'selected' : '' }}>
                                        Exclusive (পণ্যের মূল্যের সাথে যোগ হবে)
                                    </option>
                                    <option value="inclusive" {{ (old('default_tax_calculation_type', $settings['default_tax_calculation_type'] ?? '') === 'inclusive') ? 'selected' : '' }}>
                                        Inclusive (পণ্যের মূল্যের ভেতর অন্তর্ভুক্ত)
                                    </option>
                                </select>
                            </div>

                            <!-- Delivery VAT Switch -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1 d-block">Shipping Charges</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="apply_vat_on_shipping" value="1" id="applyShippingVat" {{ !empty($settings['apply_vat_on_shipping']) ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark small fw-medium" for="applyShippingVat">ডেলিভারি ফি-এর ওপর ভ্যাট ধার্য করুন</label>
                                </div>
                                <span class="text-secondary d-block mt-1" style="font-size: 11px;">অন করলে কুরিয়ার চার্জের ওপরও ভ্যাট যোগ হবে।</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Details & BIN Card -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-building text-primary"></i> Business &amp; Registration Details
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Company BIN (মূসক ৯.১ ও চালানের জন্য)</label>
                                <input type="text" name="company_bin" class="form-control form-control-sm font-monospace text-uppercase" value="{{ old('company_bin', $settings['company_bin'] ?? '') }}" placeholder="001293847-0101">
                                <span class="text-secondary d-block mt-1" style="font-size: 11px;">ব্রাঞ্চে নির্দিষ্ট BIN না থাকলে এটি প্রদর্শিত হবে।</span>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Registered Entity Name</label>
                                <input type="text" name="vat_registration_name" class="form-control form-control-sm" value="{{ old('vat_registration_name', $settings['vat_registration_name'] ?? '') }}" placeholder="e.g. Shop Now BD Online">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small mb-1">Tax Circle / Area Division</label>
                                <input type="text" name="vat_circle_or_division" class="form-control form-control-sm" value="{{ old('vat_circle_or_division', $settings['vat_circle_or_division'] ?? '') }}" placeholder="e.g. Circle-14, Taxes Zone-08, Dhaka">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Display, Automation & POS Rules -->
            <div class="col-12 col-lg-5">
                <!-- Display & POS Card -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-desktop text-primary"></i> POS &amp; Display Rules
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">
                            <div class="col-12">
                                <div class="form-check form-switch p-2 border rounded-1 bg-light">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="display_vat_breakdown_on_checkout" value="1" id="checkoutBreakdownSwitch" {{ !empty($settings['display_vat_breakdown_on_checkout']) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="checkoutBreakdownSwitch">
                                        চেকআউটে ভ্যাটের পৃথক ব্রেকডাউন দেখান
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-2 border rounded-1 bg-light">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="enable_pos_vat" value="1" id="posVatSwitch" {{ !empty($settings['enable_pos_vat']) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="posVatSwitch">
                                        পিওএস টার্মিনালে ভ্যাট সক্রিয় রাখুন
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark small mb-1">POS Receipt Format <span class="text-danger">*</span></label>
                                <select name="pos_vat_display_mode" class="form-select form-select-sm" required>
                                    <option value="item_breakdown" {{ (old('pos_vat_display_mode', $settings['pos_vat_display_mode'] ?? '') === 'item_breakdown') ? 'selected' : '' }}>
                                        Item-wise Details (প্রতিটি আইটেমের ভ্যাটসহ)
                                    </option>
                                    <option value="summary_only" {{ (old('pos_vat_display_mode', $settings['pos_vat_display_mode'] ?? '') === 'summary_only') ? 'selected' : '' }}>
                                        Summary Total Only (নিচে শুধু মোট ভ্যাট)
                                    </option>
                                    <option value="hidden" {{ (old('pos_vat_display_mode', $settings['pos_vat_display_mode'] ?? '') === 'hidden') ? 'selected' : '' }}>
                                        Hide VAT on Receipt (রিসিটে ভ্যাট লুকান)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rounding & Ledger Locking Card -->
                <div class="card border border-light-subtle shadow-sm bg-white mb-2 mb-md-3">
                    <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-lock text-primary"></i> Rounding &amp; Period Locking
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 g-md-3">
                            <div class="col-12">
                                <div class="form-check form-switch p-2 border rounded-1 bg-light">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="enable_round_off_vat" value="1" id="roundOffVatSwitch" {{ !empty($settings['enable_round_off_vat']) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="roundOffVatSwitch">
                                        ট্যাক্স ২ দশমিক স্থানে রাউন্ড করুন (Round to 2 Decimals)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-2 border rounded-1 bg-light mb-1">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="auto_lock_tax_periods" value="1" id="autoLockPeriodSwitch" {{ !empty($settings['auto_lock_tax_periods']) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark small" for="autoLockPeriodSwitch">
                                        মাস শেষে পূর্ববর্তী ট্যাক্স লেজার লক করুন
                                    </label>
                                </div>
                                <span class="text-secondary d-block ps-1" style="font-size: 11px;">
                                    এনবিআর অডিট সুরক্ষার জন্য মাস শেষ হলে পুরনো রিপোর্ট পরিবর্তন বন্ধ রাখবে।
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button Card -->
                <div class="card border border-light-subtle shadow-sm bg-white">
                    <div class="card-body p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-save"></i> <span>সেটিংস সংরক্ষণ করুন (Save Settings)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Strict 5px Radius Rule */
    .tax-settings-wrapper .card,
    .tax-settings-wrapper .btn,
    .tax-settings-wrapper .form-control,
    .tax-settings-wrapper .form-select,
    .tax-settings-wrapper .input-group-text,
    .tax-settings-wrapper .alert,
    .tax-settings-wrapper .rounded,
    .tax-settings-wrapper .rounded-1 {
        border-radius: 5px !important;
    }

    /* Input Focus State & Micro Typography */
    .tax-settings-wrapper .form-control:focus,
    .tax-settings-wrapper .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    }

    /* Switch Styling */
    .tax-settings-wrapper .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    /* Responsive Mobile Protection */
    @media (max-width: 575.98px) {
        .tax-settings-wrapper {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-settings-wrapper .card-body {
            padding: 0.75rem !important;
        }

        .tax-settings-wrapper .form-label {
            font-size: 0.8rem !important;
        }

        .tax-settings-wrapper .form-check-label {
            font-size: 0.8rem !important;
            word-break: break-word;
        }
    }
</style>

<script>
    function handleVatSwitchToggle(el) {
        const card = document.getElementById('vatSwitchCard');
        const label = document.getElementById('vatSwitchLabel');
        const help = document.getElementById('vatSwitchHelp');

        if (el.checked) {
            card.style.background = '#f0fdf4';
            card.style.borderColor = '#bbf7d0';
            label.innerText = 'VAT System: Active (সক্রিয়)';
            help.innerText = 'ওয়েবসাইট, পিওএস ও অর্ডারে স্বয়ংক্রিয়ভাবে ভ্যাট হিসাব করা হচ্ছে।';
        } else {
            card.style.background = '#fff1f2';
            card.style.borderColor = '#fecdd3';
            label.innerText = 'VAT System: Disabled (নিষ্ক্রিয়)';
            help.innerText = 'পুরো সিস্টেমের ভ্যাট বন্ধ রয়েছে (কোনো ভ্যাট ধার্য হবে না)।';
        }
    }
</script>
@endsection