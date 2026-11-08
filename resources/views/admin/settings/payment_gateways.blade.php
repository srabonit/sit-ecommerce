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
    /* =====================================================
       PAYMENT SETTINGS — ADMIN DESIGN SYSTEM
    ===================================================== */

    #paymentSettingsRoot {
        --pay-primary: #2563eb;
        --pay-primary-soft: #eff6ff;
        --pay-border: #e2e8f0;
        --pay-muted: #64748b;
        --pay-text: #0f172a;
        --pay-surface: #ffffff;
        --pay-soft: #f8fafc;
        color: var(--pay-text);
        font-size: 13px;
    }

    /* Strict 5px radius */
    #paymentSettingsRoot *,
    #addManualModal *,
    #editManualModal *,
    #deleteManualMethodModal *,
    #paymentNoticeModal * {
        border-radius: 5px;
    }

    #paymentSettingsRoot .btn,
    #paymentSettingsRoot .form-control,
    #paymentSettingsRoot .form-select,
    #paymentSettingsRoot .badge,
    #paymentSettingsRoot .alert,
    #paymentSettingsRoot .gateway-item-box,
    #paymentSettingsRoot .qr-thumb-img,
    #addManualModal .btn,
    #addManualModal .form-control,
    #addManualModal .modal-content,
    #editManualModal .btn,
    #editManualModal .form-control,
    #editManualModal .modal-content,
    #deleteManualMethodModal .modal-content,
    #paymentNoticeModal .modal-content {
        border-radius: 5px !important;
    }

    /* Page heading */
    #paymentSettingsRoot .page-heading {
        font-size: 18px;
        font-weight: 750;
        color: #0f172a;
        letter-spacing: -.35px;
    }

    #paymentSettingsRoot .page-description {
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    /* Compact buttons */
    #paymentSettingsRoot .btn-compact,
    #addManualModal .btn-compact,
    #editManualModal .btn-compact,
    #deleteManualMethodModal .btn-compact,
    #paymentNoticeModal .btn-compact {
        min-height: 30px;
        padding: 5px 12px !important;
        font-size: 11.5px !important;
        font-weight: 650 !important;
        line-height: 1.3 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #paymentSettingsRoot .btn-primary,
    #addManualModal .btn-primary,
    #editManualModal .btn-primary {
        background: var(--pay-primary);
        border-color: var(--pay-primary);
    }

    #paymentSettingsRoot .btn-primary:hover,
    #addManualModal .btn-primary:hover,
    #editManualModal .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    /* Action buttons */
    #paymentSettingsRoot .action-sq-btn {
        width: 29px !important;
        height: 29px !important;
        min-width: 29px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border-radius: 5px !important;
        transition: all .15s ease;
    }

    #paymentSettingsRoot .action-sq-btn:hover {
        background: #eff6ff;
        border-color: #bfdbfe !important;
    }

    /* Main panels */
    #paymentSettingsRoot .payment-panel-card {
        height: 100%;
        background: var(--pay-surface);
        border: 1px solid var(--pay-border);
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .045);
        overflow: hidden;
    }

    #paymentSettingsRoot .payment-panel-header {
        padding: 14px 16px;
        background: #fff;
        border-bottom: 1px solid var(--pay-border);
    }

    #paymentSettingsRoot .panel-title {
        font-size: 13.5px;
        font-weight: 750;
        color: #172033;
        margin: 0;
    }

    #paymentSettingsRoot .panel-subtitle {
        display: block;
        color: #94a3b8;
        font-size: 11px;
        margin-top: 4px;
    }

    #paymentSettingsRoot .panel-icon {
        width: 31px;
        height: 31px;
        min-width: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 13px;
        border-radius: 5px;
    }

    /* Table */
    #paymentSettingsRoot .custom-payment-table {
        margin: 0;
        width: 100%;
    }

    #paymentSettingsRoot .custom-payment-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 10.5px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .045em;
        border-bottom: 1px solid #e2e8f0;
        padding: 11px 12px;
        white-space: nowrap;
    }

    #paymentSettingsRoot .custom-payment-table td {
        padding: 11px 12px;
        vertical-align: middle;
        font-size: 12px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    #paymentSettingsRoot .custom-payment-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #paymentSettingsRoot .custom-payment-table tbody tr:hover {
        background: #f8fafc;
    }

    #paymentSettingsRoot .method-name {
        color: #172033;
        font-size: 12.5px;
        font-weight: 700;
    }

    #paymentSettingsRoot .account-number {
        color: #2563eb;
        font-size: 11.5px;
        font-weight: 700;
        font-family: monospace;
        white-space: nowrap;
    }

    #paymentSettingsRoot .qr-thumb-img {
        width: 38px;
        height: 38px;
        padding: 3px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #fff;
        cursor: pointer;
        transition: transform .15s ease;
    }

    #paymentSettingsRoot .qr-thumb-img:hover {
        transform: scale(1.06);
    }

    /* Gateway accordion */
    #paymentSettingsRoot .gateway-item-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #paymentSettingsRoot .gateway-item-box:hover {
        border-color: #cbd5e1;
    }

    #paymentSettingsRoot .gateway-item-box.active-open {
        border-color: #93c5fd !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, .07);
    }

    #paymentSettingsRoot .gateway-collapse-header {
        min-height: 54px;
        padding: 10px 13px;
        background: #fff;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: background .15s ease;
    }

    #paymentSettingsRoot .gateway-collapse-header:hover {
        background: #f8fafc;
    }

    #paymentSettingsRoot .gateway-item-box.active-open .gateway-collapse-header {
        background: #eff6ff;
        border-bottom: 1px solid #dbeafe;
    }

    #paymentSettingsRoot .gateway-name {
        font-size: 12.5px;
        font-weight: 750;
        color: #1e293b;
    }

    #paymentSettingsRoot .gw-chevron-icon {
        width: 27px;
        height: 27px;
        min-width: 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 10px;
        transition: transform .2s ease, background .15s ease, color .15s ease;
    }

    #paymentSettingsRoot .gateway-item-box.active-open .gw-chevron-icon {
        transform: rotate(180deg);
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    #paymentSettingsRoot .gateway-collapse-body {
        padding: 15px;
        background: #fff;
    }

    /* Form controls */
    #paymentSettingsRoot .form-control,
    #paymentSettingsRoot .form-select,
    #addManualModal .form-control,
    #editManualModal .form-control {
        min-height: 34px;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #0f172a;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #paymentSettingsRoot textarea.form-control,
    #addManualModal textarea.form-control,
    #editManualModal textarea.form-control {
        min-height: auto;
        resize: vertical;
    }

    #paymentSettingsRoot .form-control:focus,
    #paymentSettingsRoot .form-select:focus,
    #addManualModal .form-control:focus,
    #editManualModal .form-control:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #paymentSettingsRoot .form-label-compact,
    #addManualModal .form-label-compact,
    #editManualModal .form-label-compact {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 11.5px;
        font-weight: 700;
    }

    #paymentSettingsRoot .form-check-input,
    #addManualModal .form-check-input,
    #editManualModal .form-check-input {
        cursor: pointer;
    }

    #paymentSettingsRoot .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Gateway dropdown */
    #paymentSettingsRoot #gatewayQuickDropdown {
        min-height: 35px;
        font-size: 11.5px;
        border-color: #cbd5e1;
    }

    /* QR media picker */
    #addManualModal .qr-picker-surface,
    #editManualModal .qr-picker-surface {
        padding: 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 5px !important;
        transition: border-color .15s ease;
    }

    #addManualModal .qr-picker-surface:hover,
    #editManualModal .qr-picker-surface:hover {
        border-color: #60a5fa;
    }

    #addManualModal .qr-picker-actions,
    #editManualModal .qr-picker-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    /* Gateway footer */
    #paymentSettingsRoot .gateway-footer-bar {
        padding-top: 12px !important;
        border-top: 1px solid #e2e8f0 !important;
    }

    /* Empty state */
    #paymentSettingsRoot .empty-state {
        padding: 42px 15px;
        text-align: center;
        color: #94a3b8;
    }

    #paymentSettingsRoot .empty-state-icon {
        width: 45px;
        height: 45px;
        margin: 0 auto 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 18px;
    }

    /* Modals */
    #addManualModal .modal-content,
    #editManualModal .modal-content,
    #deleteManualMethodModal .modal-content,
    #paymentNoticeModal .modal-content {
        border: 1px solid #e2e8f0 !important;
        border-radius: 5px !important;
        overflow: hidden;
    }

    #addManualModal .modal-header,
    #editManualModal .modal-header,
    #addManualModal .modal-footer,
    #editManualModal .modal-footer {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    #addManualModal .modal-title,
    #editManualModal .modal-title {
        font-size: 14px;
        font-weight: 750;
    }

    #addManualModal .modal-body,
    #editManualModal .modal-body {
        padding: 17px;
    }

    #addManualModal .modal-footer,
    #editManualModal .modal-footer {
        padding: 10px 15px;
    }

    #deleteManualMethodModal .modal-body,
    #paymentNoticeModal .modal-body {
        padding: 18px 15px;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #paymentSettingsRoot .payment-panel-card {
            height: auto;
        }
    }

    @media (max-width: 767.98px) {
        #paymentSettingsRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #paymentSettingsRoot .page-heading {
            font-size: 17px;
        }

        #paymentSettingsRoot .top-action-btn-wrap {
            width: 100%;
        }

        #paymentSettingsRoot .top-action-btn-wrap .btn {
            width: 100%;
            min-height: 36px;
        }

        #paymentSettingsRoot .payment-panel-header {
            padding: 12px;
        }

        #paymentSettingsRoot .gateway-collapse-header {
            padding: 10px;
        }

        #paymentSettingsRoot .gateway-collapse-body {
            padding: 12px;
        }

        #paymentSettingsRoot .gateway-footer-bar {
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: center !important;
        }

        #paymentSettingsRoot .custom-payment-table th,
        #paymentSettingsRoot .custom-payment-table td {
            padding: 9px 10px;
            font-size: 11.5px;
        }

        #addManualModal .qr-picker-actions,
        #editManualModal .qr-picker-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        #addManualModal .qr-picker-actions .btn,
        #editManualModal .qr-picker-actions .btn {
            width: 100%;
            white-space: normal;
            text-align: center;
        }

        #addManualModal .modal-dialog,
        #editManualModal .modal-dialog {
            margin: 10px;
        }
    }

    @media (max-width: 420px) {
        #paymentSettingsRoot .gateway-collapse-header > div {
            gap: 5px !important;
        }

        #paymentSettingsRoot .gateway-footer-bar .btn {
            padding-left: 9px !important;
            padding-right: 9px !important;
        }

        #paymentSettingsRoot .gateway-footer-bar .form-check-label {
            font-size: 10.5px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="paymentSettingsRoot">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="panel-icon">
                    <i class="fas fa-wallet"></i>
                </span>
                <h5 class="page-heading mb-0">Payment Gateways</h5>
            </div>
            <p class="page-description mb-0">
                Configure manual payments & payment gateways.
            </p>
        </div>

        <div class="top-action-btn-wrap">
            <button type="button"
                    class="btn btn-primary btn-compact shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#addManualModal">
                <i class="fas fa-plus"></i>
                Add Manual Method
            </button>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border mb-3 py-2 px-3 d-flex align-items-center justify-content-between"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span class="fw-semibold small">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">

        {{-- =====================================================
             MANUAL PAYMENT METHODS
        ====================================================== --}}
        <div class="col-12 col-lg-6">
            <div class="payment-panel-card d-flex flex-column">

                <div class="payment-panel-header d-flex justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="panel-icon">
                            <i class="fas fa-qrcode"></i>
                        </span>
                        <div>
                            <h6 class="panel-title">Manual Payment Methods</h6>
                            <small class="panel-subtitle">Methods available at customer checkout</small>
                        </div>
                    </div>

                    <span class="badge bg-light text-dark border px-2 py-2 font-monospace">
                        {{ count($manualMethods) }} Methods
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table custom-payment-table align-middle text-nowrap">
                            <thead>
                                <tr>
                                    <th class="ps-3">Name</th>
                                    <th>Account</th>
                                    <th>QR</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($manualMethods as $m)
                                    @php
                                        $qrSrc = $resolvePaymentQr($m->qr_code_image ?? null);
                                    @endphp

                                    <tr>
                                        <td class="ps-3">
                                            <span class="method-name d-block">{{ $m->name }}</span>
                                        </td>

                                        <td>
                                            <span class="account-number">{{ $m->account_number }}</span>
                                        </td>

                                        <td>
                                            @if(!empty($qrSrc))
                                                <img src="{{ $qrSrc }}"
                                                     alt="QR"
                                                     class="qr-thumb-img"
                                                     onerror="this.onerror=null; this.src='https://placehold.co/44x44?text=QR';"
                                                     onclick="window.open(this.src, '_blank')"
                                                     loading="lazy"
                                                     title="Click to view full QR">
                                            @else
                                                <span class="text-muted small">None</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <form action="{{ route('admin.payment.toggle_manual', $m->id) }}"
                                                  method="POST"
                                                  class="m-0">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-compact py-1 px-2 {{ $m->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                        style="font-size:10px !important; min-height:25px;">
                                                    {{ $m->is_active ? 'Active' : 'Off' }}
                                                </button>
                                            </form>
                                        </td>

                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <button type="button"
                                                        class="btn btn-light border text-primary action-sq-btn"
                                                        onclick='openEditManualModal(@json($m), "{{ $qrSrc ?? '' }}")'
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button type="button"
                                                        class="btn btn-light border text-danger action-sq-btn"
                                                        onclick="openDeleteManualModal('{{ route('admin.payment.destroy_manual', $m->id) }}', '{{ addslashes($m->name) }}')"
                                                        title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="fas fa-qrcode"></i>
                                                </div>
                                                <div class="fw-bold text-dark small">No manual methods found</div>
                                                <small class="d-block mt-1">Add a manual payment method to get started.</small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
             AUTOMATED PAYMENT GATEWAYS
        ====================================================== --}}
        <div class="col-12 col-lg-6">
            <div class="payment-panel-card d-flex flex-column">

                <div class="payment-panel-header">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="panel-icon">
                                <i class="fas fa-credit-card"></i>
                            </span>
                            <div>
                                <h6 class="panel-title">Payment Gateways</h6>
                                <small class="panel-subtitle">Configure gateway API credentials</small>
                            </div>
                        </div>
                    </div>

                    {{-- Gateway Selector --}}
                    <div class="d-flex align-items-center gap-2">
                        <select id="gatewayQuickDropdown"
                                class="form-select form-select-sm fw-semibold flex-grow-1"
                                onchange="handleGatewayDropdownSelect(this.value)">
                            <option value="all">Select Gateway — Show All</option>

                            @foreach($gateways as $gw)
                                <option value="{{ $gw->id }}">
                                    {{ $gw->name }} ({{ strtoupper($gw->code) }}) — {{ $gw->is_active ? 'Active' : 'Disabled' }}
                                </option>
                            @endforeach
                        </select>

                        <button type="button"
                                class="btn btn-light border btn-compact text-secondary"
                                onclick="collapseAllGateways()"
                                title="Collapse All">
                            <i class="fas fa-compress-alt"></i>
                            <span>Collapse</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-2 p-md-3 d-flex flex-column gap-2" id="gatewayAccordionList">

                    @forelse($gateways as $gw)
                        @php
                            $creds = json_decode($gw->credentials, true) ?: [];
                        @endphp

                        <div class="gateway-item-box"
                             id="gatewayCardBox_{{ $gw->id }}"
                             data-gw-id="{{ $gw->id }}">

                            {{-- Gateway Header --}}
                            <div class="gateway-collapse-header"
                                 onclick="toggleGatewayPanel({{ $gw->id }})">

                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="gateway-name">{{ $gw->name }}</span>

                                    <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                        {{ strtoupper($gw->code) }}
                                    </span>

                                    @if($gw->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            Enabled
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            Off
                                        </span>
                                    @endif

                                    @if($gw->is_live)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                            Live
                                        </span>
                                    @endif
                                </div>

                                <span class="gw-chevron-icon">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </div>

                            {{-- Gateway Configuration --}}
                            <div class="gateway-collapse-body d-none"
                                 id="gatewayCollapseBody_{{ $gw->id }}">

                                <form action="{{ route('admin.payment.update_gateway', $gw->id) }}"
                                      method="POST"
                                      class="m-0">
                                    @csrf

                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <span class="small fw-bold text-secondary">
                                            <i class="fas fa-key me-1"></i>
                                            API Credentials
                                        </span>

                                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0"
                                                   type="checkbox"
                                                   name="is_active"
                                                   id="gw_active_{{ $gw->id }}"
                                                   value="1"
                                                   {{ $gw->is_active ? 'checked' : '' }}>

                                            <label class="form-check-label fw-bold small text-dark"
                                                   for="gw_active_{{ $gw->id }}">
                                                Enable Gateway
                                            </label>
                                        </div>
                                    </div>

                                    {{-- bKash --}}
                                    @if($gw->code === 'bkash')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">App Key</label>
                                                <input type="text"
                                                       name="credentials[app_key]"
                                                       value="{{ $creds['app_key'] ?? '' }}"
                                                       class="form-control form-control-sm"
                                                       placeholder="App key">
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">App Secret</label>
                                                <input type="password"
                                                       name="credentials[app_secret]"
                                                       value="{{ $creds['app_secret'] ?? '' }}"
                                                       class="form-control form-control-sm"
                                                       placeholder="App secret">
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Username</label>
                                                <input type="text"
                                                       name="credentials[username]"
                                                       value="{{ $creds['username'] ?? '' }}"
                                                       class="form-control form-control-sm"
                                                       placeholder="Username">
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Password</label>
                                                <input type="password"
                                                       name="credentials[password]"
                                                       value="{{ $creds['password'] ?? '' }}"
                                                       class="form-control form-control-sm"
                                                       placeholder="Password">
                                            </div>
                                        </div>

                                    {{-- Nagad --}}
                                    @elseif($gw->code === 'nagad')
                                        <div class="mb-2">
                                            <label class="form-label-compact">Merchant ID</label>
                                            <input type="text"
                                                   name="credentials[merchant_id]"
                                                   value="{{ $creds['merchant_id'] ?? '' }}"
                                                   class="form-control form-control-sm"
                                                   placeholder="Merchant ID">
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label-compact">PG Public Key</label>
                                            <textarea name="credentials[public_key]"
                                                      rows="2"
                                                      class="form-control form-control-sm font-monospace"
                                                      placeholder="Public key">{{ $creds['public_key'] ?? '' }}</textarea>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label-compact">Merchant Private Key</label>
                                            <textarea name="credentials[private_key]"
                                                      rows="2"
                                                      class="form-control form-control-sm font-monospace"
                                                      placeholder="Private key">{{ $creds['private_key'] ?? '' }}</textarea>
                                        </div>

                                    {{-- Upay --}}
                                    @elseif($gw->code === 'upay')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Merchant ID</label>
                                                <input type="text"
                                                       name="credentials[merchant_id]"
                                                       value="{{ $creds['merchant_id'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Merchant Key</label>
                                                <input type="text"
                                                       name="credentials[merchant_key]"
                                                       value="{{ $creds['merchant_key'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Merchant Secret</label>
                                                <input type="password"
                                                       name="credentials[merchant_secret]"
                                                       value="{{ $creds['merchant_secret'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>
                                        </div>

                                    {{-- SSLCommerz --}}
                                    @elseif($gw->code === 'sslcommerz')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Store ID</label>
                                                <input type="text"
                                                       name="credentials[store_id]"
                                                       value="{{ $creds['store_id'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Store Password</label>
                                                <input type="password"
                                                       name="credentials[store_password]"
                                                       value="{{ $creds['store_password'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>
                                        </div>

                                    {{-- EPS --}}
                                    @elseif($gw->code === 'eps')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Merchant ID</label>
                                                <input type="text"
                                                       name="credentials[merchant_id]"
                                                       value="{{ $creds['merchant_id'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Client Secret</label>
                                                <input type="password"
                                                       name="credentials[client_secret]"
                                                       value="{{ $creds['client_secret'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>
                                        </div>

                                    {{-- Shurjopay --}}
                                    @elseif($gw->code === 'shurjopay')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Username</label>
                                                <input type="text"
                                                       name="credentials[username]"
                                                       value="{{ $creds['username'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Password</label>
                                                <input type="password"
                                                       name="credentials[password]"
                                                       value="{{ $creds['password'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-4">
                                                <label class="form-label-compact">Prefix</label>
                                                <input type="text"
                                                       name="credentials[prefix]"
                                                       value="{{ $creds['prefix'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>
                                        </div>

                                    {{-- Stripe --}}
                                    @elseif($gw->code === 'stripe')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Publishable Key</label>
                                                <input type="text"
                                                       name="credentials[publishable_key]"
                                                       value="{{ $creds['publishable_key'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Secret Key</label>
                                                <input type="password"
                                                       name="credentials[secret_key]"
                                                       value="{{ $creds['secret_key'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>
                                        </div>

                                    {{-- PayPal --}}
                                    @elseif($gw->code === 'paypal')
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Client ID</label>
                                                <input type="text"
                                                       name="credentials[client_id]"
                                                       value="{{ $creds['client_id'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="form-label-compact">Client Secret</label>
                                                <input type="password"
                                                       name="credentials[client_secret]"
                                                       value="{{ $creds['client_secret'] ?? '' }}"
                                                       class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Gateway Footer --}}
                                    <div class="gateway-footer-bar d-flex justify-content-between align-items-center gap-2 mt-3">
                                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0"
                                                   type="checkbox"
                                                   name="is_live"
                                                   id="gw_live_{{ $gw->id }}"
                                                   value="1"
                                                   {{ $gw->is_live ? 'checked' : '' }}>

                                            <label class="form-check-label text-muted small"
                                                   for="gw_live_{{ $gw->id }}">
                                                Live Mode
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-compact px-3 shadow-sm">
                                            <i class="fas fa-check"></i>
                                            Save {{ $gw->name }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="fw-bold text-dark small">No gateways found</div>
                            <small class="d-block mt-1">No automated payment gateways are available.</small>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>
</div>


{{-- =========================================================
     ADD MANUAL PAYMENT METHOD MODAL
========================================================= --}}
<div class="modal fade" id="addManualModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.payment.store_manual') }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content shadow-lg">
            @csrf

            <input type="hidden" name="qr_code_image_media" id="addQrMediaInput" value="">

            <div class="modal-header py-2 px-3">
                <h6 class="modal-title text-dark mb-0">
                    <i class="fas fa-plus-circle text-primary me-1"></i>
                    Add Manual Method
                </h6>
                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">
                            Method Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control form-control-sm"
                               placeholder="bKash Merchant"
                               required>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">
                            Account Number <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="account_number"
                               class="form-control form-control-sm font-monospace bn-digit"
                               placeholder="017XXXXXXXX"
                               required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">QR Standee Image</label>

                    <div class="qr-picker-surface">
                        <div class="qr-picker-actions mb-2">
                            <button type="button"
                                    class="btn btn-primary btn-compact flex-grow-1"
                                    onclick="chooseAddManualQrMedia()">
                                <i class="fas fa-photo-video"></i>
                                Choose from Library
                            </button>

                            <label class="btn btn-outline-secondary btn-compact mb-0 flex-grow-1"
                                   style="cursor:pointer;">
                                <i class="fas fa-upload"></i>
                                Upload File
                                <input type="file"
                                       name="qr_code_image"
                                       id="addQrFileInput"
                                       class="d-none"
                                       accept="image/*"
                                       onchange="previewAddQrFile(this)">
                            </label>
                        </div>

                        <small class="text-muted d-block text-center" style="font-size:10.5px;">
                            Select an existing QR image or upload from your device.
                        </small>

                        <div id="add_qr_preview" class="mt-2 d-none"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">Payment Instruction</label>
                    <textarea name="instruction"
                              rows="3"
                              class="form-control form-control-sm"
                              placeholder="Step-by-step payment instructions for customers..."></textarea>
                </div>

                <div class="form-check form-switch m-0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           id="add_active"
                           value="1"
                           checked>

                    <label class="form-check-label fw-semibold small text-dark"
                           for="add_active">
                        Active on Checkout
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light border btn-compact px-3"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-primary btn-compact px-3">
                    <i class="fas fa-save"></i>
                    Save Method
                </button>
            </div>
        </form>
    </div>
</div>


{{-- =========================================================
     EDIT MANUAL PAYMENT METHOD MODAL
========================================================= --}}
<div class="modal fade" id="editManualModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editManualForm"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content shadow-lg">
            @csrf

            <input type="hidden" name="qr_code_image_media" id="editQrMediaInput" value="">
            <input type="hidden" name="remove_qr_image" id="removeEditQrImage" value="0">

            <div class="modal-header py-2 px-3">
                <h6 class="modal-title text-dark mb-0">
                    <i class="fas fa-edit text-primary me-1"></i>
                    Edit Payment Method
                </h6>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">
                            Method Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="edit_name"
                               class="form-control form-control-sm"
                               required>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label-compact">
                            Account Number <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="account_number"
                               id="edit_account_number"
                               class="form-control form-control-sm font-monospace bn-digit"
                               required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">Change QR Image</label>

                    <div class="qr-picker-surface">
                        <div class="qr-picker-actions mb-2">
                            <button type="button"
                                    class="btn btn-primary btn-compact flex-grow-1"
                                    onclick="chooseEditManualQrMedia()">
                                <i class="fas fa-photo-video"></i>
                                Choose from Library
                            </button>

                            <label class="btn btn-outline-secondary btn-compact mb-0 flex-grow-1"
                                   style="cursor:pointer;">
                                <i class="fas fa-upload"></i>
                                Upload File
                                <input type="file"
                                       name="qr_code_image"
                                       id="editQrFileInput"
                                       class="d-none"
                                       accept="image/*"
                                       onchange="previewEditQrFile(this)">
                            </label>
                        </div>

                        <div id="edit_qr_preview" class="mt-2"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label-compact">Payment Instruction</label>
                    <textarea name="instruction"
                              id="edit_instruction"
                              rows="3"
                              class="form-control form-control-sm"></textarea>
                </div>

                <div class="form-check form-switch m-0">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           id="edit_is_active"
                           value="1">

                    <label class="form-check-label fw-semibold small text-dark"
                           for="edit_is_active">
                        Active on Checkout
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light border btn-compact px-3"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-primary btn-compact px-3">
                    <i class="fas fa-save"></i>
                    Update Method
                </button>
            </div>
        </form>
    </div>
</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}
<div class="modal fade" id="deleteManualMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="d-inline-flex align-items-center justify-content-center mx-auto mb-3 bg-danger-subtle text-danger"
                     style="width:46px;height:46px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size:14.5px;">
                    Delete Payment Method?
                </h6>

                <p class="text-muted small mb-3" style="font-size:12px;line-height:1.5;">
                    Are you sure you want to delete
                    <strong id="deleteManualMethodName" class="text-dark"></strong>?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button"
                            class="btn btn-light border btn-compact px-3"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <form id="deleteManualMethodForm"
                          method="POST"
                          action=""
                          class="m-0">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger btn-compact px-3">
                            <i class="fas fa-trash-alt"></i>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- =========================================================
     NOTICE MODAL
========================================================= --}}
<div class="modal fade"
     id="paymentNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="d-inline-flex align-items-center justify-content-center mx-auto mb-3 bg-warning-subtle text-warning"
                     style="width:44px;height:44px;">
                    <i class="fas fa-exclamation-triangle fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="paymentNoticeTitle"
                    style="font-size:14px;">
                    Notice
                </h6>

                <p class="text-muted small mb-3"
                   id="paymentNoticeMsg"
                   style="font-size:12px;line-height:1.5;"></p>

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    /* =====================================================
       GATEWAY ACCORDION
    ===================================================== */

    function toggleGatewayPanel(gwId, forceOpen = null) {
        const card = document.getElementById('gatewayCardBox_' + gwId);
        const body = document.getElementById('gatewayCollapseBody_' + gwId);

        if (!card || !body) return;

        const isCurrentlyOpen = !body.classList.contains('d-none');
        const shouldOpen = forceOpen !== null ? forceOpen : !isCurrentlyOpen;

        if (shouldOpen) {
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

    function handleGatewayDropdownSelect(val) {
        const allCards = document.querySelectorAll('.gateway-item-box');

        if (val === 'all') {
            allCards.forEach(card => {
                card.classList.remove('d-none', 'active-open');
            });

            document.querySelectorAll('.gateway-collapse-body').forEach(body => {
                body.classList.add('d-none');
            });
        } else {
            allCards.forEach(card => {
                if (card.getAttribute('data-gw-id') === String(val)) {
                    card.classList.remove('d-none');
                    toggleGatewayPanel(val, true);
                } else {
                    card.classList.add('d-none');
                    card.classList.remove('active-open');
                }
            });
        }
    }

    function collapseAllGateways() {
        const selectEl = document.getElementById('gatewayQuickDropdown');

        if (selectEl) selectEl.value = 'all';

        document.querySelectorAll('.gateway-item-box').forEach(card => {
            card.classList.remove('d-none', 'active-open');
        });

        document.querySelectorAll('.gateway-collapse-body').forEach(body => {
            body.classList.add('d-none');
        });
    }


    /* =====================================================
       NOTICE & DELETE MODALS
    ===================================================== */

    function showPaymentNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('paymentNoticeTitle');
        const msgEl = document.getElementById('paymentNoticeMsg');
        const modalEl = document.getElementById('paymentNoticeModal');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function openDeleteManualModal(actionUrl, methodName) {
        document.getElementById('deleteManualMethodForm').action = actionUrl;
        document.getElementById('deleteManualMethodName').innerText = '"' + methodName + '"';

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('deleteManualMethodModal')
        ).show();
    }


    /* =====================================================
       MEDIA LIBRARY HELPERS
    ===================================================== */

    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;

        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;

        if (!item || typeof item !== 'object') return null;

        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl = item.url || item.file_path || item.path || '';

        if (!rawPath || rawPath === 'undefined') return null;

        return {
            path: String(rawPath).trim(),
            url: String(rawUrl).trim()
        };
    }


    /* =====================================================
       ADD METHOD — MEDIA LIBRARY
    ===================================================== */

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
                                <div class="d-flex align-items-center gap-2 bg-white border p-2">
                                    <img src="${picked.url}"
                                         class="border p-1"
                                         style="width:44px;height:44px;object-fit:cover;"
                                         onerror="this.onerror=null;this.src='https://placehold.co/44x44?text=QR';">

                                    <div class="text-truncate">
                                        <span class="small text-success fw-bold d-block" style="font-size:11.5px;">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Selected from Media Library
                                        </span>
                                        <small class="text-muted text-truncate d-block font-monospace"
                                               style="font-size:10px;max-width:180px;">
                                            ${picked.path}
                                        </small>
                                    </div>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-compact ms-auto"
                                            onclick="clearAddQrMedia()">
                                        <i class="fas fa-times"></i>
                                        Clear
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


    /* =====================================================
       ADD METHOD — LOCAL FILE PREVIEW
    ===================================================== */

    function previewAddQrFile(input) {
        if (input.files && input.files[0]) {
            document.getElementById('addQrMediaInput').value = '';

            const preview = document.getElementById('add_qr_preview');
            const fileName = input.files[0].name;
            const reader = new FileReader();

            reader.onload = function(e) {
                if (preview) {
                    preview.innerHTML = `
                        <div class="d-flex align-items-center gap-2 bg-white border p-2">
                            <img src="${e.target.result}"
                                 class="border p-1"
                                 style="width:44px;height:44px;object-fit:cover;">

                            <div class="text-truncate">
                                <span class="small text-primary fw-bold d-block" style="font-size:11.5px;">
                                    <i class="fas fa-file-image me-1"></i>
                                    Local File Selected
                                </span>
                                <small class="text-muted text-truncate d-block font-monospace"
                                       style="font-size:10px;max-width:180px;">
                                    ${fileName}
                                </small>
                            </div>

                            <button type="button"
                                    class="btn btn-outline-danger btn-compact ms-auto"
                                    onclick="clearAddQrMedia()">
                                <i class="fas fa-times"></i>
                                Clear
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


    /* =====================================================
       EDIT METHOD — MEDIA LIBRARY
    ===================================================== */

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
                                <div class="d-flex align-items-center gap-2 bg-white border p-2">
                                    <img src="${picked.url}"
                                         class="border p-1"
                                         style="width:44px;height:44px;object-fit:cover;"
                                         onerror="this.onerror=null;this.src='https://placehold.co/44x44?text=QR';">

                                    <div class="text-truncate">
                                        <span class="small text-success fw-bold d-block" style="font-size:11.5px;">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Selected from Media Library
                                        </span>
                                        <small class="text-muted text-truncate d-block font-monospace"
                                               style="font-size:10px;max-width:180px;">
                                            ${picked.path}
                                        </small>
                                    </div>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-compact ms-auto"
                                            onclick="clearEditQrMedia()">
                                        <i class="fas fa-times"></i>
                                        Clear
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


    /* =====================================================
       EDIT METHOD — LOCAL FILE PREVIEW
    ===================================================== */

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
                        <div class="d-flex align-items-center gap-2 bg-white border p-2">
                            <img src="${e.target.result}"
                                 class="border p-1"
                                 style="width:44px;height:44px;object-fit:cover;">

                            <div class="text-truncate">
                                <span class="small text-primary fw-bold d-block" style="font-size:11.5px;">
                                    <i class="fas fa-file-image me-1"></i>
                                    New File Selected
                                </span>
                                <small class="text-muted text-truncate d-block font-monospace"
                                       style="font-size:10px;max-width:180px;">
                                    ${fileName}
                                </small>
                            </div>

                            <button type="button"
                                    class="btn btn-outline-danger btn-compact ms-auto"
                                    onclick="clearEditQrMedia()">
                                <i class="fas fa-times"></i>
                                Clear
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
            preview.innerHTML = `
                <span class="small text-muted d-block text-center py-2" style="font-size:11px;">
                    <i class="fas fa-image me-1"></i>
                    No QR image selected
                </span>
            `;
        }
    }


    /* =====================================================
       OPEN EDIT MANUAL METHOD MODAL
    ===================================================== */

    function openEditManualModal(m, currentQrSrc) {
        document.getElementById('editManualForm').action =
            "{{ url('admin/payment-settings/manual') }}/" + m.id + "/update";

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
                <div class="d-flex align-items-center gap-2 bg-white border p-2">
                    <img src="${imageSrc}"
                         onerror="this.onerror=null;this.src='https://placehold.co/44x44?text=QR';"
                         class="border p-1"
                         style="width:44px;height:44px;object-fit:cover;">

                    <div>
                        <span class="small text-dark fw-bold d-block" style="font-size:11.5px;">
                            Current QR Standee
                        </span>
                        <small class="text-muted d-block" style="font-size:10px;">
                            Active on checkout
                        </small>
                    </div>

                    <button type="button"
                            class="btn btn-outline-danger btn-compact ms-auto"
                            onclick="clearEditQrMedia()">
                        <i class="fas fa-trash-alt"></i>
                        Remove
                    </button>
                </div>
            `;
        } else {
            preview.innerHTML = `
                <span class="small text-muted d-block text-center py-2" style="font-size:11px;">
                    <i class="fas fa-image me-1"></i>
                    No QR image attached
                </span>
            `;
        }

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('editManualModal')
        ).show();
    }
</script>

@endsection