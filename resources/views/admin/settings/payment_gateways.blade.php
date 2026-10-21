@extends('layouts.admin')

@section('title', 'Payment Settings - ' . config('app.name'))

@php
    $resolvePaymentQr = function($path, $fallback = null) {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback ?? '');
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim($path, '/'));
    };
@endphp

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #paymentSettingsRoot .btn,
    #paymentSettingsRoot .btn-sm,
    #paymentSettingsRoot button,
    #paymentSettingsRoot .form-control,
    #paymentSettingsRoot .form-select,
    #paymentSettingsRoot .badge,
    #paymentSettingsRoot .alert,
    #paymentSettingsRoot .qr-thumb-img,
    #paymentSettingsRoot .gateway-item-box,
    #addManualModal .btn,
    #addManualModal .form-control,
    #addManualModal .modal-content,
    #editManualModal .btn,
    #editManualModal .form-control,
    #editManualModal .modal-content,
    #deleteManualMethodModal .btn,
    #deleteManualMethodModal .modal-content,
    #paymentNoticeModal .btn,
    #paymentNoticeModal .modal-content {
        border-radius: 5px !important;
    }

    /* Compact & Sleek Button Sizing */
    #paymentSettingsRoot .btn-compact,
    #addManualModal .btn-compact,
    #editManualModal .btn-compact,
    #deleteManualMethodModal .btn-compact,
    #paymentNoticeModal .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Square 26x26 Action Buttons */
    .action-sq-btn {
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10.5px !important;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Enterprise Cards */
    .payment-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .payment-panel-header {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 7px 7px 0 0;
    }

    /* Collapsible Gateway Item Card */
    .gateway-item-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .gateway-item-box:hover {
        border-color: #cbd5e1;
    }
    .gateway-item-box.active-open {
        border-color: #2563eb !important;
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.08);
    }
    .gateway-collapse-header {
        padding: 11px 14px;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: background-color 0.15s ease;
    }
    .gateway-collapse-header:hover {
        background: #f1f5f9;
    }
    .gateway-item-box.active-open .gateway-collapse-header {
        background: #eff6ff;
        border-bottom: 1px solid #dbeafe;
    }
    .gw-chevron-icon {
        width: 24px;
        height: 24px;
        border-radius: 5px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #475569;
        transition: transform 0.2s ease, background-color 0.15s ease, color 0.15s ease;
    }
    .gateway-item-box.active-open .gw-chevron-icon {
        transform: rotate(180deg);
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .gateway-collapse-body {
        padding: 14px;
        background: #ffffff;
    }

    /* Form Controls */
    #paymentSettingsRoot .form-control,
    #paymentSettingsRoot .form-select,
    #addManualModal .form-control,
    #editManualModal .form-control {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #paymentSettingsRoot .form-control:focus,
    #paymentSettingsRoot .form-select:focus,
    #addManualModal .form-control:focus,
    #editManualModal .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    .form-label-compact {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    /* Table Styling */
    .custom-payment-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 10px 12px;
        white-space: nowrap;
    }
    .custom-payment-table td {
        padding: 10px 12px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-payment-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Upgraded Global Media Chooser Surface inside Modals */
    .qr-picker-surface {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 5px !important;
        padding: 12px;
        transition: border-color 0.15s ease;
    }
    .qr-picker-surface:hover {
        border-color: #2563eb;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* 100/100 Mobile Responsive Tuning */
    @media (max-width: 767.98px) {
        .top-action-btn-wrap {
            width: 100%;
        }
        .top-action-btn-wrap .btn-compact {
            width: 100%;
            padding: 7px 12px !important;
        }
        .qr-picker-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 6px !important;
        }
        .qr-picker-actions .btn-compact {
            width: 100%;
        }
        .gateway-footer-bar {
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: center !important;
        }
        .custom-payment-table th,
        .custom-payment-table td {
            padding: 8px 10px !important;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="paymentSettingsRoot">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center" style="font-size: 18px;">
                <i class="fas fa-wallet text-primary me-2"></i>
                <span>Payment Gateways</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Configure manual payment methods and automated merchant gateways.</small>
        </div>
        <div class="top-action-btn-wrap">
            <button type="button" class="btn btn-primary btn-compact px-3 py-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#addManualModal">
                <i class="fas fa-plus me-1"></i>
                <span>Add Manual Method</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs mb-3 small py-2 px-3 d-flex align-items-center justify-content-between" role="alert">
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 g-md-4">
        <!-- 1. Manual Payment Methods & QR -->
        <div class="col-12 col-lg-6">
            <div class="payment-panel-card h-100 d-flex flex-column">
                <div class="payment-panel-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 14px;">
                            <i class="fas fa-qrcode text-primary me-2"></i>
                            <span>Manual Methods</span>
                        </h6>
                        <small class="text-muted" style="font-size: 11px;">Active methods appear on customer checkout</small>
                    </div>
                    <span class="badge bg-white text-dark border px-2 py-1 bn-digit font-monospace" style="font-size: 11px;">{{ count($manualMethods) }} Methods</span>
                </div>

                <div class="card-body p-0 flex-grow-1">
                    <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
                        <table class="table custom-payment-table align-middle mb-0 text-nowrap">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="min-width: 110px;">Name</th>
                                    <th style="min-width: 120px;">Account</th>
                                    <th style="min-width: 65px;">QR</th>
                                    <th class="text-center" style="min-width: 75px;">Status</th>
                                    <th class="text-end pe-3" style="min-width: 85px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($manualMethods as $m)
                                    @php
                                        $qrSrc = $resolvePaymentQr($m->qr_code_image ?? null);
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <strong class="text-dark d-block" style="font-size: 12.5px;">{{ $m->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="small fw-bold text-primary font-monospace bn-digit" style="font-size: 12px;">{{ $m->account_number }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($qrSrc))
                                                <img src="{{ $qrSrc }}" alt="QR" onerror="this.onerror=null; this.src='https://placehold.co/44x44?text=QR';" class="border p-1 bg-white qr-thumb-img shadow-xs" style="width: 36px; height: 36px; object-fit: cover; cursor: pointer;" onclick="window.open(this.src, '_blank')" loading="lazy" title="Click to view full QR">
                                            @else
                                                <span class="text-muted small" style="font-size: 11px;">None</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.payment.toggle_manual', $m->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-compact {{ $m->is_active ? 'btn-success' : 'btn-secondary' }} py-1 px-2" style="font-size: 10px !important; padding: 2px 8px !important;">
                                                    {{ $m->is_active ? 'Active' : 'Off' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end pe-3 text-nowrap">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <button type="button" class="btn btn-light border text-primary action-sq-btn shadow-xs" onclick='openEditManualModal(@json($m), "{{ $qrSrc ?? '' }}")' title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-light border text-danger action-sq-btn shadow-xs" onclick="openDeleteManualModal('{{ route('admin.payment.destroy_manual', $m->id) }}', '{{ addslashes($m->name) }}')" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted small">
                                            <i class="fas fa-qrcode fa-2x mb-2 d-block opacity-25"></i>
                                            <span class="fw-semibold">No manual methods found.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Automated Gateways (Collapsible + Quick Dropdown Selector) -->
        <div class="col-12 col-lg-6">
            <div class="payment-panel-card h-100 d-flex flex-column">
                <div class="payment-panel-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 14px;">
                                <i class="fas fa-hand-holding-dollar text-info me-2"></i>
                                <span>Automated Payment Gateways</span>
                            </h6>
                            <small class="text-muted" style="font-size: 11px;">Select or click any gateway below to configure API credentials</small>
                        </div>
                        <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1" style="font-size: 10px;">API Pay</span>
                    </div>

                    <!-- Quick Dropdown Selector for Gateways -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1">
                            <select id="gatewayQuickDropdown" class="form-select form-select-sm fw-semibold" onchange="handleGatewayDropdownSelect(this.value)">
                                <option value="all">-- Select Gateway to Configure (Show All) --</option>
                                @foreach($gateways as $gw)
                                    <option value="{{ $gw->id }}">
                                        {{ $gw->name }} ({{ strtoupper($gw->code) }}) — {{ $gw->is_active ? 'Active' : 'Disabled' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-light border btn-compact px-2 text-secondary" onclick="collapseAllGateways()" title="Collapse All">
                            <i class="fas fa-compress-alt me-1"></i>
                            <span>Collapse</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-2 p-md-3 d-flex flex-column gap-2" id="gatewayAccordionList">
                    @forelse($gateways as $gw)
                        @php
                            $creds = json_decode($gw->credentials, true) ?: [];
                        @endphp
                        <div class="gateway-item-box" id="gatewayCardBox_{{ $gw->id }}" data-gw-id="{{ $gw->id }}">
                            <!-- Collapsible Trigger Header -->
                            <div class="gateway-collapse-header" onclick="toggleGatewayPanel({{ $gw->id }})">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">{{ $gw->name }}</h6>
                                    <span class="badge bg-white text-primary border font-monospace px-2 py-1" style="font-size: 9.5px;">{{ strtoupper($gw->code) }}</span>
                                    @if($gw->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 9.5px;">Enabled</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 9.5px;">Off</span>
                                    @endif
                                    @if($gw->is_live)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1" style="font-size: 9.5px;">Live</span>
                                    @endif
                                </div>
                                <span class="gw-chevron-icon">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </div>

                            <!-- Collapsible Form Body (Collapsed by default) -->
                            <div class="gateway-collapse-body d-none" id="gatewayCollapseBody_{{ $gw->id }}">
                                <form action="{{ route('admin.payment.update_gateway', $gw->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                        <span class="small text-muted fw-semibold" style="font-size: 11px;">API Credentials Configuration</span>
                                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0" type="checkbox" name="is_active" id="gw_active_{{ $gw->id }}" value="1" {{ $gw->is_active ? 'checked' : '' }} style="cursor: pointer;">
                                            <label class="form-check-label fw-bold small text-dark" for="gw_active_{{ $gw->id }}" style="font-size: 11.5px; cursor: pointer;">Enable Gateway</label>
                                        </div>
                                    </div>

                                    {{-- bKash --}}
                                    @if($gw->code === 'bkash')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">App Key</label>
                                                <input type="text" name="credentials[app_key]" value="{{ $creds['app_key'] ?? '' }}" class="form-control form-control-sm" placeholder="App key">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">App Secret</label>
                                                <input type="password" name="credentials[app_secret]" value="{{ $creds['app_secret'] ?? '' }}" class="form-control form-control-sm" placeholder="App secret">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Username</label>
                                                <input type="text" name="credentials[username]" value="{{ $creds['username'] ?? '' }}" class="form-control form-control-sm" placeholder="Username">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Password</label>
                                                <input type="password" name="credentials[password]" value="{{ $creds['password'] ?? '' }}" class="form-control form-control-sm" placeholder="Password">
                                            </div>
                                        </div>
                                    {{-- Nagad --}}
                                    @elseif($gw->code === 'nagad')
                                        <div class="mb-2">
                                            <label class="form-label-compact">Merchant ID</label>
                                            <input type="text" name="credentials[merchant_id]" value="{{ $creds['merchant_id'] ?? '' }}" class="form-control form-control-sm" placeholder="Merchant ID">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label-compact">PG Public Key</label>
                                            <textarea name="credentials[public_key]" rows="2" class="form-control form-control-sm font-monospace" placeholder="Public key">{{ $creds['public_key'] ?? '' }}</textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label-compact">Merchant Private Key</label>
                                            <textarea name="credentials[private_key]" rows="2" class="form-control form-control-sm font-monospace" placeholder="Private key">{{ $creds['private_key'] ?? '' }}</textarea>
                                        </div>
                                    {{-- Upay --}}
                                    @elseif($gw->code === 'upay')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Merchant ID</label>
                                                <input type="text" name="credentials[merchant_id]" value="{{ $creds['merchant_id'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Merchant Key</label>
                                                <input type="text" name="credentials[merchant_key]" value="{{ $creds['merchant_key'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Merchant Secret</label>
                                                <input type="password" name="credentials[merchant_secret]" value="{{ $creds['merchant_secret'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    {{-- SSLCommerz --}}
                                    @elseif($gw->code === 'sslcommerz')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Store ID</label>
                                                <input type="text" name="credentials[store_id]" value="{{ $creds['store_id'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Store Password</label>
                                                <input type="password" name="credentials[store_password]" value="{{ $creds['store_password'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    {{-- EPS --}}
                                    @elseif($gw->code === 'eps')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Merchant ID</label>
                                                <input type="text" name="credentials[merchant_id]" value="{{ $creds['merchant_id'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Client Secret</label>
                                                <input type="password" name="credentials[client_secret]" value="{{ $creds['client_secret'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    {{-- Shurjopay --}}
                                    @elseif($gw->code === 'shurjopay')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Username</label>
                                                <input type="text" name="credentials[username]" value="{{ $creds['username'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Password</label>
                                                <input type="password" name="credentials[password]" value="{{ $creds['password'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Prefix</label>
                                                <input type="text" name="credentials[prefix]" value="{{ $creds['prefix'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    {{-- Stripe --}}
                                    @elseif($gw->code === 'stripe')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Publishable Key</label>
                                                <input type="text" name="credentials[publishable_key]" value="{{ $creds['publishable_key'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Secret Key</label>
                                                <input type="password" name="credentials[secret_key]" value="{{ $creds['secret_key'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    {{-- PayPal --}}
                                    @elseif($gw->code === 'paypal')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Client ID</label>
                                                <input type="text" name="credentials[client_id]" value="{{ $creds['client_id'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Client Secret</label>
                                                <input type="password" name="credentials[client_secret]" value="{{ $creds['client_secret'] ?? '' }}" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="gateway-footer-bar d-flex justify-content-between align-items-center gap-2 mt-3 pt-2 border-top">
                                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0" type="checkbox" name="is_live" id="gw_live_{{ $gw->id }}" value="1" {{ $gw->is_live ? 'checked' : '' }} style="cursor: pointer;">
                                            <label class="form-check-label text-muted small" for="gw_live_{{ $gw->id }}" style="font-size: 11.5px; cursor: pointer;">Live Mode</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">
                                            <i class="fas fa-check me-1" style="font-size: 10px;"></i>
                                            <span>Save {{ $gw->name }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted small">No gateways found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= ADD MANUAL MODAL ================= -->
<div class="modal fade" id="addManualModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.payment.store_manual') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg overflow-hidden">
            @csrf
            <input type="hidden" name="qr_code_image_media" id="addQrMediaInput" value="">

            <div class="modal-header bg-light border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 14px;">
                    <i class="fas fa-plus-circle text-primary me-1"></i> Add Manual Method
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">Method Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder=" bKash Merchant" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control form-control-sm font-monospace bn-digit" placeholder="017XXXXXXXX" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">QR Standee Image (Global Media Chooser)</label>
                    <div class="qr-picker-surface">
                        <div class="qr-picker-actions d-flex align-items-center gap-2 mb-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs flex-grow-1" onclick="chooseAddManualQrMedia()">
                                <i class="fas fa-photo-video me-1"></i>
                                <span>Choose from Media Library</span>
                            </button>
                            <label class="btn btn-outline-secondary btn-compact shadow-xs mb-0 flex-grow-1" style="cursor: pointer;">
                                <i class="fas fa-upload me-1"></i>
                                <span>Upload Local File</span>
                                <input type="file" name="qr_code_image" id="addQrFileInput" class="d-none" accept="image/*" onchange="previewAddQrFile(this)">
                            </label>
                        </div>
                        <small class="text-muted d-block text-center" style="font-size: 10.5px;">Select an existing QR image from Media Library or upload from device.</small>
                        <div id="add_qr_preview" class="mt-2 d-none"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">Payment Instruction</label>
                    <textarea name="instruction" rows="3" class="form-control form-control-sm" placeholder="Step-by-step payment instructions for customers..."></textarea>
                </div>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="add_active" value="1" checked style="cursor: pointer;">
                    <label class="form-check-label fw-semibold small text-dark" for="add_active" style="font-size: 12px; cursor: pointer;">Active on Checkout</label>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-3">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">Save Method</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT MANUAL MODAL ================= -->
<div class="modal fade" id="editManualModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editManualForm" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg overflow-hidden">
            @csrf
            <input type="hidden" name="qr_code_image_media" id="editQrMediaInput" value="">
            <input type="hidden" name="remove_qr_image" id="removeEditQrImage" value="0">

            <div class="modal-header bg-light border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 14px;">
                    <i class="fas fa-edit text-primary me-1"></i> Edit Payment Method
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">Method Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" id="edit_account_number" class="form-control form-control-sm font-monospace bn-digit" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">Change QR Image (Global Media Chooser)</label>
                    <div class="qr-picker-surface">
                        <div class="qr-picker-actions d-flex align-items-center gap-2 mb-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs flex-grow-1" onclick="chooseEditManualQrMedia()">
                                <i class="fas fa-photo-video me-1"></i>
                                <span>Choose from Media Library</span>
                            </button>
                            <label class="btn btn-outline-secondary btn-compact shadow-xs mb-0 flex-grow-1" style="cursor: pointer;">
                                <i class="fas fa-upload me-1"></i>
                                <span>Upload Local File</span>
                                <input type="file" name="qr_code_image" id="editQrFileInput" class="d-none" accept="image/*" onchange="previewEditQrFile(this)">
                            </label>
                        </div>
                        <div id="edit_qr_preview" class="mt-2"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">Payment Instruction</label>
                    <textarea name="instruction" id="edit_instruction" rows="3" class="form-control form-control-sm"></textarea>
                </div>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1" style="cursor: pointer;">
                    <label class="form-check-label fw-semibold small text-dark" for="edit_is_active" style="font-size: 12px; cursor: pointer;">Active on Checkout</label>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-3">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">Update Method</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= DELETE CONFIRMATION MODAL ================= -->
<div class="modal fade" id="deleteManualMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Payment Method?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to delete <strong id="deleteManualMethodName" class="text-dark"></strong>?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteManualMethodForm" method="POST" action="" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact px-3 shadow-xs">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= NOTICE MODAL ================= -->
<div class="modal fade" id="paymentNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-exclamation-triangle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="paymentNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="paymentNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle Single Gateway Accordion Panel
    function toggleGatewayPanel(gwId, forceOpen = null) {
        const card = document.getElementById('gatewayCardBox_' + gwId);
        const body = document.getElementById('gatewayCollapseBody_' + gwId);
        if (!card || !body) return;

        const isCurrentlyOpen = !body.classList.contains('d-none');
        const shouldOpen = forceOpen !== null ? forceOpen : !isCurrentlyOpen;

        if (shouldOpen) {
            // Close other open panels for clean accordion behavior
            document.querySelectorAll('.gateway-item-box').forEach(el => {
                el.classList.remove('active-open');
            });
            document.querySelectorAll('.gateway-collapse-body').forEach(el => {
                el.classList.add('d-none');
            });

            body.classList.remove('d-none');
            card.classList.add('active-open');
        } else {
            body.classList.add('d-none');
            card.classList.remove('active-open');
        }
    }

    // Handle Top Gateway Dropdown Selection
    function handleGatewayDropdownSelect(val) {
        const allCards = document.querySelectorAll('.gateway-item-box');
        if (val === 'all') {
            allCards.forEach(c => {
                c.classList.remove('d-none', 'active-open');
            });
            document.querySelectorAll('.gateway-collapse-body').forEach(b => b.classList.add('d-none'));
        } else {
            allCards.forEach(c => {
                if (c.getAttribute('data-gw-id') === String(val)) {
                    c.classList.remove('d-none');
                    toggleGatewayPanel(val, true);
                } else {
                    c.classList.add('d-none');
                    c.classList.remove('active-open');
                }
            });
        }
    }

    function collapseAllGateways() {
        const selectEl = document.getElementById('gatewayQuickDropdown');
        if (selectEl) selectEl.value = 'all';
        document.querySelectorAll('.gateway-item-box').forEach(c => {
            c.classList.remove('d-none', 'active-open');
        });
        document.querySelectorAll('.gateway-collapse-body').forEach(b => {
            b.classList.add('d-none');
        });
    }

    function showPaymentNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('paymentNoticeTitle');
        const msgEl = document.getElementById('paymentNoticeMsg');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        const modalEl = document.getElementById('paymentNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function openDeleteManualModal(actionUrl, methodName) {
        document.getElementById('deleteManualMethodForm').action = actionUrl;
        document.getElementById('deleteManualMethodName').innerText = '"' + methodName + '"';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteManualMethodModal')).show();
    }

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

    function chooseAddManualQrMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('addQrMediaInput').value = picked.path;
                        const fileInput = document.getElementById('addQrFileInput');
                        if (fileInput) fileInput.value = '';

                        const preview = document.getElementById('add_qr_preview');
                        if (preview) {
                            preview.innerHTML = `
                                <div class="d-flex align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <img src="${picked.url}" class="border p-1" style="width: 44px; height: 44px; object-fit: cover; border-radius: 5px;" onerror="this.onerror=null; this.src='https://placehold.co/44x44?text=QR';">
                                    <div class="text-truncate">
                                        <span class="small text-success fw-bold d-block" style="font-size: 11.5px;"><i class="fas fa-check-circle me-1"></i>Selected from Media Library</span>
                                        <small class="text-muted text-truncate d-block font-monospace" style="font-size: 10px; max-width: 180px;">${picked.path}</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearAddQrMedia()">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
                            `;
                            preview.classList.remove('d-none');
                        }
                    }
                }
            });
        } else {
            showPaymentNotice('Media Library is loading, please try again.');
        }
    }

    function previewAddQrFile(input) {
        if (input.files && input.files[0]) {
            document.getElementById('addQrMediaInput').value = '';
            const preview = document.getElementById('add_qr_preview');
            const fileName = input.files[0].name;
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.innerHTML = `
                        <div class="d-flex align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                            <img src="${e.target.result}" class="border p-1" style="width: 44px; height: 44px; object-fit: cover; border-radius: 5px;">
                            <div class="text-truncate">
                                <span class="small text-primary fw-bold d-block" style="font-size: 11.5px;"><i class="fas fa-file-image me-1"></i>Local File Selected</span>
                                <small class="text-muted text-truncate d-block font-monospace" style="font-size: 10px; max-width: 180px;">${fileName}</small>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearAddQrMedia()">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                        </div>
                    `;
                    preview.classList.remove('d-none');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearAddQrMedia() {
        const fileInput = document.getElementById('addQrFileInput');
        if (fileInput) fileInput.value = '';
        document.getElementById('addQrMediaInput').value = '';
        const preview = document.getElementById('add_qr_preview');
        if (preview) {
            preview.innerHTML = '';
            preview.classList.add('d-none');
        }
    }

    function chooseEditManualQrMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('editQrMediaInput').value = picked.path;
                        const fileInput = document.getElementById('editQrFileInput');
                        if (fileInput) fileInput.value = '';
                        document.getElementById('removeEditQrImage').value = '0';

                        const preview = document.getElementById('edit_qr_preview');
                        if (preview) {
                            preview.innerHTML = `
                                <div class="d-flex align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <img src="${picked.url}" class="border p-1" style="width: 44px; height: 44px; object-fit: cover; border-radius: 5px;" onerror="this.onerror=null; this.src='https://placehold.co/44x44?text=QR';">
                                    <div class="text-truncate">
                                        <span class="small text-success fw-bold d-block" style="font-size: 11.5px;"><i class="fas fa-check-circle me-1"></i>Selected from Media Library</span>
                                        <small class="text-muted text-truncate d-block font-monospace" style="font-size: 10px; max-width: 180px;">${picked.path}</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearEditQrMedia()">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
                            `;
                        }
                    }
                }
            });
        } else {
            showPaymentNotice('Media Library is loading, please try again.');
        }
    }

    function previewEditQrFile(input) {
        if (input.files && input.files[0]) {
            document.getElementById('editQrMediaInput').value = '';
            document.getElementById('removeEditQrImage').value = '0';
            const preview = document.getElementById('edit_qr_preview');
            const fileName = input.files[0].name;
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.innerHTML = `
                        <div class="d-flex align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                            <img src="${e.target.result}" class="border p-1" style="width: 44px; height: 44px; object-fit: cover; border-radius: 5px;">
                            <div class="text-truncate">
                                <span class="small text-primary fw-bold d-block" style="font-size: 11.5px;"><i class="fas fa-file-image me-1"></i>New File Selected</span>
                                <small class="text-muted text-truncate d-block font-monospace" style="font-size: 10px; max-width: 180px;">${fileName}</small>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearEditQrMedia()">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                        </div>
                    `;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearEditQrMedia() {
        const fileInput = document.getElementById('editQrFileInput');
        if (fileInput) fileInput.value = '';
        document.getElementById('editQrMediaInput').value = '';
        document.getElementById('removeEditQrImage').value = '1';
        const preview = document.getElementById('edit_qr_preview');
        if (preview) {
            preview.innerHTML = '<span class="small text-muted d-block text-center py-1" style="font-size: 11px;">No QR image selected</span>';
        }
    }

    function openEditManualModal(m, currentQrSrc) {
        document.getElementById('editManualForm').action = "{{ url('admin/payment-settings/manual') }}/" + m.id + "/update";
        document.getElementById('edit_name').value = m.name;
        document.getElementById('edit_account_number').value = m.account_number;
        document.getElementById('edit_instruction').value = m.instruction || '';
        document.getElementById('edit_is_active').checked = (m.is_active == 1);
        document.getElementById('editQrMediaInput').value = '';
        document.getElementById('removeEditQrImage').value = '0';

        const fileInput = document.getElementById('editQrFileInput');
        if (fileInput) fileInput.value = '';

        const preview = document.getElementById('edit_qr_preview');
        let imageSrc = currentQrSrc;
        
        if (!imageSrc && m.qr_code_image) {
            let path = m.qr_code_image.replace(/^\/+/, '');
            if (path.startsWith('http://') || path.startsWith('https://')) {
                imageSrc = path;
            } else if (path.startsWith('uploads/') || path.startsWith('storage/')) {
                imageSrc = "{{ asset('') }}" + path;
            } else {
                imageSrc = "{{ asset('uploads/') }}/" + path;
            }
        }

        if (imageSrc) {
            preview.innerHTML = `
                <div class="d-flex align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                    <img src="${imageSrc}" onerror="this.onerror=null; this.src='https://placehold.co/44x44?text=QR';" class="border p-1" style="width: 44px; height: 44px; object-fit: cover; border-radius: 5px;">
                    <div>
                        <span class="small text-dark fw-bold d-block" style="font-size: 11.5px;">Current QR Standee</span>
                        <small class="text-muted d-block" style="font-size: 10px;">Active on checkout</small>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearEditQrMedia()">
                        <i class="fas fa-trash-alt me-1"></i>Remove
                    </button>
                </div>
            `;
        } else {
            preview.innerHTML = '<span class="small text-muted d-block text-center py-1" style="font-size: 11px;">No QR image attached</span>';
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('editManualModal')).show();
    }
</script>
@endsection