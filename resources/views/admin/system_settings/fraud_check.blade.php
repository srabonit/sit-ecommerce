
@extends('layouts.admin')

@section('title', 'FraudBD API Configuration')

@section('admin_content')

<style>
    /* =====================================================
       KAIADMIN LITE - CLASSIC WHITE DESIGN
       FRAUDBD CONFIGURATION
    ===================================================== */

    #fraudConfigRoot {
        --fraud-primary: var(--bs-primary, #1572e8);
        --fraud-heading: #2a2f5b;
        --fraud-text: #495057;
        --fraud-muted: #8d9498;
        --fraud-border: #e6e8eb;
        --fraud-surface: #ffffff;
        --fraud-soft: #f8f9fc;

        width: 100%;
        min-width: 0;
        color: var(--fraud-text);
        font-size: 13px;
    }

    #fraudConfigRoot * {
        box-sizing: border-box;
    }

    /* Consistent Radius */

    #fraudConfigRoot .btn,
    #fraudConfigRoot .form-control,
    #fraudConfigRoot .form-select,
    #fraudConfigRoot .alert,
    #fraudConfigRoot .card {
        border-radius: 5px !important;
    }

    /* Page Header */

    #fraudConfigRoot .fraud-page-header {
        background: var(--fraud-surface);
        border: 1px solid var(--fraud-border);
        border-radius: 5px;
        padding: 14px 17px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .025);
    }

    #fraudConfigRoot .fraud-page-title {
        color: var(--fraud-heading);
        font-size: clamp(16px, 1.5vw, 19px);
        font-weight: 700;
        line-height: 1.4;
        margin: 0;
    }

    #fraudConfigRoot .fraud-page-subtitle {
        color: var(--fraud-muted);
        font-size: 12px;
        display: block;
        margin-top: 3px;
    }

    /* Main Configuration Card */

    #fraudConfigRoot .fraud-config-card {
        background: #ffffff;
        border: 1px solid var(--fraud-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035) !important;
        padding: 22px;
        width: 100%;
    }

    #fraudConfigRoot .fraud-card-header {
        padding-bottom: 15px;
        margin-bottom: 19px;
        border-bottom: 1px solid var(--fraud-border);
    }

    #fraudConfigRoot .fraud-card-title {
        color: var(--fraud-heading);
        font-size: 14px;
        font-weight: 700;
        margin: 0;
    }

    #fraudConfigRoot .fraud-card-description {
        color: var(--fraud-muted);
        font-size: 11.5px;
        margin-top: 4px;
    }

    /* Gateway Status */

    #fraudConfigRoot .fraud-status-panel {
        background: #f8faff;
        border: 1px solid #e7edf7;
        border-radius: 5px;
        padding: 13px 15px;
        min-height: 58px;
    }

    #fraudConfigRoot .fraud-status-title {
        color: var(--fraud-heading);
        font-size: 12px;
        font-weight: 700;
    }

    #fraudConfigRoot .fraud-status-description {
        display: block;
        color: var(--fraud-muted);
        font-size: 10.5px;
        margin-top: 2px;
    }

    #fraudConfigRoot .form-check-input {
        cursor: pointer;
        box-shadow: none;
        border-color: #ced4da;
    }

    #fraudConfigRoot .form-check-input:checked {
        background-color: var(--fraud-primary);
        border-color: var(--fraud-primary);
    }

    #fraudConfigRoot .form-check-input:focus {
        border-color: var(--fraud-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .12);
    }

    #fraudConfigRoot .form-check-label {
        cursor: pointer;
        color: var(--fraud-text);
        font-size: 11.5px;
    }

    /* Form Fields */

    #fraudConfigRoot .form-label {
        display: block;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    #fraudConfigRoot .form-control {
        min-height: 39px;
        padding: 8px 11px;
        background: #ffffff;
        border: 1px solid #ebedf2;
        color: #495057;
        font-size: 12.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #fraudConfigRoot .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #fraudConfigRoot .form-control:focus {
        border-color: var(--fraud-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .10);
    }

    #fraudConfigRoot .form-control.font-monospace {
        font-size: 12px;
        font-weight: 500;
    }

    #fraudConfigRoot .invalid-feedback {
        font-size: 11px;
        margin-top: 5px;
    }

    /* Alerts */

    #fraudConfigRoot .alert {
        font-size: 12px;
        line-height: 1.5;
        border-width: 1px;
        padding: 11px 14px;
    }

    #fraudConfigRoot .alert ul {
        padding-left: 19px;
    }

    #fraudConfigRoot .alert li + li {
        margin-top: 3px;
    }

    #fraudConfigRoot .btn-close {
        box-shadow: none;
    }

    /* Buttons */

    #fraudConfigRoot .fraud-back-btn {
        min-height: 35px;
        font-size: 11.5px;
        font-weight: 600;
        padding: 7px 13px;
        white-space: nowrap;
    }

    #fraudConfigRoot .fraud-save-btn {
        min-height: 39px;
        padding: 9px 23px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 5px !important;
        background: var(--fraud-primary);
        border-color: var(--fraud-primary);
        transition: filter .15s ease;
    }

    #fraudConfigRoot .fraud-save-btn:hover {
        filter: brightness(.94);
    }

    /* Form Footer */

    #fraudConfigRoot .fraud-form-footer {
        padding-top: 17px;
        margin-top: 5px;
        border-top: 1px solid var(--fraud-border);
    }

    /* Tablet */

    @media (max-width: 991.98px) {
        #fraudConfigRoot .fraud-config-card {
            padding: 19px;
        }
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #fraudConfigRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #fraudConfigRoot .fraud-page-header {
            padding: 12px;
        }

        #fraudConfigRoot .fraud-page-title {
            font-size: 16px;
        }

        #fraudConfigRoot .fraud-config-card {
            padding: 15px;
        }

        #fraudConfigRoot .fraud-card-header {
            margin-bottom: 16px;
        }

        #fraudConfigRoot .fraud-status-panel {
            padding: 11px;
        }

        #fraudConfigRoot .fraud-back-btn {
            width: 100%;
            justify-content: center;
        }

        #fraudConfigRoot .fraud-form-footer {
            padding-top: 14px;
        }

        #fraudConfigRoot .fraud-save-btn {
            width: 100%;
        }
    }

    /* Small Mobile */

    @media (max-width: 380px) {
        #fraudConfigRoot .fraud-config-card {
            padding: 12px;
        }

        #fraudConfigRoot .fraud-page-title {
            font-size: 15px;
        }

        #fraudConfigRoot .fraud-status-panel {
            padding: 10px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="fraudConfigRoot">

    <!-- Page Header -->
    <div class="fraud-page-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">

        <div>
            <h5 class="fraud-page-title">
                <i class="fas fa-shield-halved text-primary me-1"></i>
                Fraud Checker Configuration
            </h5>

            <span class="fraud-page-subtitle">
                Manage FraudBD API integration and verification settings.
            </span>
        </div>

        <a href="{{ route('admin.order.fraud') }}"
           class="btn btn-outline-secondary fraud-back-btn d-inline-flex align-items-center">

            <i class="fas fa-arrow-left me-2"></i>
            Back to Fraud Checker

        </a>

    </div>

    <!-- Success Alert -->
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show mb-3"
             role="alert">

            <i class="fas fa-check-circle me-2"></i>
            <span class="fw-semibold">{{ session('success') }}</span>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif

    <!-- Validation Errors -->
    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show mb-3"
             role="alert">

            <div class="fw-bold mb-2">
                <i class="fas fa-exclamation-circle me-1"></i>
                Please review the following errors:
            </div>

            <ul class="mb-0 ps-3">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif

    <!-- Configuration Form -->
    <div class="row justify-content-center">

        <div class="col-12 col-md-12 col-lg-12 col-xl-12">

            <div class="fraud-config-card">

                <form action="{{ route('admin.system.setting.fraud.update') }}"
                      method="POST">

                    @csrf

                    <!-- Card Header -->
                    <div class="fraud-card-header">

                        <div class="d-flex align-items-center gap-2">

                            <div class="d-flex align-items-center justify-content-center rounded-1 bg-primary-subtle text-primary"
                                 style="width: 36px; height: 36px;">

                                <i class="fas fa-shield-alt"></i>

                            </div>

                            <div>

                                <h6 class="fraud-card-title">
                                    FraudBD Gateway
                                </h6>

                                <div class="fraud-card-description">
                                    API connection and authentication settings.
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Gateway Status -->
                    <div class="fraud-status-panel d-flex justify-content-between align-items-center gap-3 mb-4">

                        <div>

                            <div class="fraud-status-title">
                                Gateway Status
                            </div>

                            <span class="fraud-status-description">
                                Enable or disable fraud verification.
                            </span>

                        </div>

                        <div class="form-check form-switch m-0 flex-shrink-0">

                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_active"
                                   id="isActiveSwitch"
                                   value="1"
                                   {{ old('is_active', $setting->is_active ?? true) ? 'checked' : '' }}>

                            <label class="form-check-label fw-semibold ms-1"
                                   for="isActiveSwitch">
                                Enabled
                            </label>

                        </div>

                    </div>

                    <!-- Registered Email -->
                    <div class="mb-3">

                        <label class="form-label">
                            Registered Email
                            <span class="text-danger">*</span>
                        </label>

                        <input type="email"
                               name="api_email"
                               class="form-control @error('api_email') is-invalid @enderror"
                               value="{{ old('api_email', $setting->api_email ?? '') }}"
                               placeholder="Enter registered email address"
                               required>

                        @error('api_email')
                            <div class="invalid-feedback fw-semibold">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- API Key -->
                    <div class="mb-3">

                        <label class="form-label">
                            Active API Key
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="api_key"
                               class="form-control font-monospace @error('api_key') is-invalid @enderror"
                               value="{{ old('api_key', $setting->api_key ?? '') }}"
                               placeholder="Enter your API key"
                               required>

                        @error('api_key')
                            <div class="invalid-feedback fw-semibold">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- API Endpoint -->
                    <div class="mb-4">

                        <label class="form-label">
                            API Endpoint URL
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="api_url"
                               class="form-control font-monospace @error('api_url') is-invalid @enderror"
                               value="{{ old('api_url', $setting->api_url ?? 'https://fraudbd.com/api/check-courier-info') }}"
                               placeholder="Enter API endpoint URL"
                               required>

                        @error('api_url')
                            <div class="invalid-feedback fw-semibold">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- Form Footer -->
                    <div class="fraud-form-footer">

                        <button type="submit"
                                class="btn btn-primary fraud-save-btn">

                            <i class="fas fa-save me-2"></i>
                            Save Configuration

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection
