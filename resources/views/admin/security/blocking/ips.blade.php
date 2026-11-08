
@extends('layouts.admin')

@section('title', 'Blocked IP Management')

@section('admin_content')

<style>
/* =========================================================
   BLOCKED IP MANAGEMENT
   Kaiadmin Lite v1.2.0 - Classic White Design
   Page-scoped styling | Bootstrap 5 compatible
========================================================= */

#security-blocking-wrapper {
    --security-primary: var(--bs-primary, #1572e8);
    --security-danger: var(--bs-danger, #f25961);
    --security-success: var(--bs-success, #31ce36);
    --security-warning: var(--bs-warning, #ffad46);
    --security-info: var(--bs-info, #48abf7);
    --security-text: #2a2f5b;
    --security-muted: #8d9498;
    --security-border: #e6e9ef;
    --security-bg: #f8f9fa;
    --security-radius: 5px;

    color: var(--security-text);
    min-width: 0;
    width: 100%;
    padding-top: 20px;
    padding-bottom: 24px;
}

#security-blocking-wrapper *,
#security-blocking-wrapper *::before,
#security-blocking-wrapper *::after {
    box-sizing: border-box;
}

#security-blocking-wrapper .card {
    border: 1px solid var(--security-border) !important;
    border-radius: var(--security-radius) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .035) !important;
    background: #fff;
}

#security-blocking-wrapper .card-body {
    min-width: 0;
}

#security-blocking-wrapper .security-page-heading {
    color: var(--security-text);
    font-size: clamp(19px, 2vw, 23px);
    font-weight: 700;
    letter-spacing: -.35px;
}

#security-blocking-wrapper .security-page-heading i {
    font-size: 19px;
}

#security-blocking-wrapper .security-header {
    border-color: var(--security-border) !important;
    padding-bottom: 16px !important;
}

#security-blocking-wrapper .security-header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

#security-blocking-wrapper .btn {
    border-radius: var(--security-radius);
    font-weight: 600;
    transition: background-color .2s ease, border-color .2s ease,
                box-shadow .2s ease;
}

#security-blocking-wrapper .security-header-actions .btn {
    min-height: 35px;
    padding: 7px 11px;
    font-size: 12px;
    white-space: nowrap;
}

#security-blocking-wrapper .btn:focus-visible,
#security-blocking-wrapper .form-control:focus-visible,
#security-blocking-wrapper .form-select:focus-visible {
    outline: 2px solid rgba(21, 114, 232, .25);
    outline-offset: 2px;
}

#security-blocking-wrapper .btn-danger {
    background-color: var(--security-danger);
    border-color: var(--security-danger);
}

#security-blocking-wrapper .btn-danger:hover {
    filter: brightness(.94);
}

#security-blocking-wrapper .btn-outline-secondary {
    border-color: #dfe3e8;
    color: #5c6570;
}

#security-blocking-wrapper .btn-outline-secondary:hover {
    background: #f4f6f9;
    border-color: #cbd2da;
    color: var(--security-text);
}

#security-blocking-wrapper .security-metric-card {
    position: relative;
    overflow: hidden;
    min-height: 108px;
    padding: 17px !important;
}

#security-blocking-wrapper .security-metric-card .metric-label {
    display: block;
    color: var(--security-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .45px;
    text-transform: uppercase;
    line-height: 1.5;
}

#security-blocking-wrapper .security-metric-card h3 {
    font-size: clamp(23px, 2.5vw, 29px);
    line-height: 1.25;
    letter-spacing: -.6px;
}

#security-blocking-wrapper .security-metric-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: var(--security-radius);
}

#security-blocking-wrapper .security-metric-icon i {
    font-size: 21px;
}

#security-blocking-wrapper .security-filter-card .card-body {
    padding: 17px !important;
}

#security-blocking-wrapper .form-label {
    color: #5d6671;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 6px;
}

#security-blocking-wrapper .form-control,
#security-blocking-wrapper .form-select {
    min-height: 36px;
    border-color: #e0e3e8;
    border-radius: var(--security-radius);
    font-size: 12.5px;
    color: var(--security-text);
    box-shadow: none;
}

#security-blocking-wrapper .form-control::placeholder {
    color: #a4aab2;
    opacity: 1;
}

#security-blocking-wrapper .form-control:focus,
#security-blocking-wrapper .form-select:focus {
    border-color: var(--security-primary);
    box-shadow: 0 0 0 2px rgba(21, 114, 232, .09);
}

#security-blocking-wrapper .input-group-text {
    min-height: 36px;
    border-color: #e0e3e8;
    border-radius: var(--security-radius) 0 0 var(--security-radius);
}

#security-blocking-wrapper .input-group .form-control {
    border-radius: 0 var(--security-radius) var(--security-radius) 0;
}

#security-blocking-wrapper .security-table-card {
    overflow: hidden;
}

#security-blocking-wrapper .security-table-card .table-responsive {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

#security-blocking-wrapper .security-table {
    width: 100%;
    min-width: 1000px;
    margin-bottom: 0;
    color: #555d69;
    font-size: 12px;
    vertical-align: middle;
}

#security-blocking-wrapper .security-table thead th {
    padding: 12px 10px;
    background: #f8f9fa;
    color: #6c757d;
    border-bottom: 1px solid var(--security-border);
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .35px;
    text-transform: uppercase;
    white-space: nowrap;
}

#security-blocking-wrapper .security-table tbody td {
    padding: 11px 10px;
    border-color: #eef0f3;
    line-height: 1.5;
}

#security-blocking-wrapper .security-table tbody tr:last-child td {
    border-bottom: 0;
}

#security-blocking-wrapper .security-table tbody tr:hover > * {
    background-color: #f9fbfd;
}

#security-blocking-wrapper .security-table .font-monospace {
    font-size: 12px;
    font-weight: 600;
}

#security-blocking-wrapper .security-table .btn {
    font-size: 11px;
    padding: 5px 9px;
}

#security-blocking-wrapper .security-table .badge {
    border-radius: 4px;
    padding: 5px 7px;
    font-size: 10px;
    font-weight: 600;
}

#security-blocking-wrapper .security-table-card .card-footer {
    border-top: 1px solid var(--security-border) !important;
}

#security-blocking-wrapper #showing-entries-text {
    color: var(--security-muted);
    font-size: 12px;
}

#security-blocking-wrapper #showing-entries-text strong {
    color: var(--security-text);
}

#security-blocking-wrapper #pagination-container {
    max-width: 100%;
    overflow-x: auto;
}

#security-blocking-wrapper #pagination-container .pagination {
    margin-bottom: 0;
    flex-wrap: wrap;
    gap: 3px;
}

#security-blocking-wrapper #pagination-container .page-link {
    border-radius: 4px !important;
    border-color: var(--security-border);
    color: var(--security-text);
    font-size: 12px;
    padding: 6px 10px;
}

#security-blocking-wrapper #pagination-container .active .page-link {
    background-color: var(--security-primary);
    border-color: var(--security-primary);
    color: #fff;
}

#security-blocking-wrapper #table-blocked-ips-body {
    transition: opacity .2s ease;
}

/* Modal design */

#modalAddBlockedIp .modal-content,
#modalEmergencyFlush .modal-content {
    border: 1px solid var(--security-border) !important;
    border-radius: 6px;
    overflow: hidden;
}

#modalAddBlockedIp .modal-header,
#modalEmergencyFlush .modal-header {
    padding: 14px 18px !important;
    border-bottom: 1px solid var(--security-border);
}

#modalAddBlockedIp .modal-title,
#modalEmergencyFlush .modal-title {
    font-size: 15px;
}

#modalAddBlockedIp .modal-body,
#modalEmergencyFlush .modal-body {
    padding: 20px !important;
}

#modalAddBlockedIp .modal-footer,
#modalEmergencyFlush .modal-footer {
    padding: 12px 18px !important;
    border-top: 1px solid var(--security-border);
}

#modalAddBlockedIp .form-control,
#modalAddBlockedIp .form-select,
#modalEmergencyFlush .form-control {
    min-height: 39px;
    font-size: 13px;
}

#modalAddBlockedIp textarea.form-control {
    min-height: auto;
}

#modalAddBlockedIp .modal-footer .btn,
#modalEmergencyFlush .modal-footer .btn {
    min-height: 34px;
    padding: 7px 14px;
    font-size: 12px;
}

/* Responsive layout */

@media (max-width: 1199.98px) {
    #security-blocking-wrapper .security-header {
        align-items: flex-start !important;
    }

    #security-blocking-wrapper .security-header-actions {
        width: 100%;
    }
}

@media (max-width: 767.98px) {
    #security-blocking-wrapper {
        padding: 14px 12px 20px !important;
    }

    #security-blocking-wrapper .security-header {
        gap: 12px !important;
        margin-bottom: 18px !important;
    }

    #security-blocking-wrapper .security-header-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    #security-blocking-wrapper .security-header-actions .btn {
        width: 100%;
        justify-content: center;
        padding: 8px 7px;
        font-size: 11px;
    }

    #security-blocking-wrapper .security-header-actions #btn-refresh-blocked-ips {
        grid-column: span 2;
    }

    #security-blocking-wrapper .security-metric-card {
        min-height: 96px;
        padding: 13px !important;
    }

    #security-blocking-wrapper .security-metric-icon {
        width: 40px;
        height: 40px;
    }

    #security-blocking-wrapper .security-metric-icon i {
        font-size: 18px;
    }

    #security-blocking-wrapper .security-filter-card .card-body {
        padding: 13px !important;
    }

    #security-blocking-wrapper .security-table-card .card-footer {
        align-items: flex-start !important;
        flex-direction: column;
    }

    #security-blocking-wrapper #pagination-container {
        width: 100%;
    }

    #security-blocking-wrapper #pagination-container .pagination {
        justify-content: flex-start;
    }
}

@media (max-width: 575.98px) {
    #security-blocking-wrapper .security-page-heading {
        font-size: 19px;
    }

    #security-blocking-wrapper .row.g-3 {
        --bs-gutter-x: 10px;
        --bs-gutter-y: 10px;
    }

    #security-blocking-wrapper .security-metric-card .metric-label {
        font-size: 9.5px;
        letter-spacing: .2px;
    }

    #security-blocking-wrapper .security-metric-card h3 {
        font-size: 23px;
    }

    #security-blocking-wrapper .security-filter-card .row {
        --bs-gutter-x: 10px;
        --bs-gutter-y: 12px;
    }

    #security-blocking-wrapper .security-table-card .card-footer {
        padding: 12px !important;
    }

    #modalAddBlockedIp .modal-dialog,
    #modalEmergencyFlush .modal-dialog {
        margin: 10px;
    }

    #modalAddBlockedIp .modal-body,
    #modalEmergencyFlush .modal-body {
        padding: 15px !important;
    }
}

@media (max-width: 359.98px) {
    #security-blocking-wrapper .security-header-actions .btn {
        font-size: 10px;
        padding-left: 5px;
        padding-right: 5px;
    }

    #security-blocking-wrapper .security-metric-card {
        padding: 10px !important;
    }

    #security-blocking-wrapper .security-metric-icon {
        width: 34px;
        height: 34px;
    }
}

/* Modal viewport safety */

#modalAddBlockedIp .modal-dialog,
#modalEmergencyFlush .modal-dialog {
    max-width: calc(100% - 20px);
}

#modalAddBlockedIp .modal-content,
#modalEmergencyFlush .modal-content {
    max-height: calc(100vh - 20px);
    overflow-y: auto;
}
</style>

<div class="container-fluid px-4 py-3" id="security-blocking-wrapper">

    {{-- Header --}}
    <div class="security-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">

        <div>
            <h4 class="security-page-heading mb-0 d-flex align-items-center">
                <i class="fas fa-ban text-danger me-2"></i>
                Blocked IPs
            </h4>
        </div>

        <div class="security-header-actions">

            <a href="{{ route('admin.security.blocking.networks') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-sitemap me-1 text-danger"></i>
                Blocked Networks
            </a>

            <a href="{{ route('admin.security.blocking.users') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-user-lock me-1 text-warning"></i>
                Restricted Users
            </a>

            <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddBlockedIp">
                <i class="fas fa-plus-circle me-1"></i>
                Block IP
            </button>

            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEmergencyFlush">
                <i class="fas fa-triangle-exclamation me-1"></i>
                Emergency Flush
            </button>

            <button type="button" class="btn btn-sm btn-white border shadow-sm text-secondary px-2.5" id="btn-refresh-blocked-ips" title="Refresh">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
            </button>

        </div>
    </div>

    {{-- Metrics --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6 col-6">
            <div class="card security-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <span class="metric-label">Active Blocks</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1" id="stat-active-blocks">{{ number_format($stats['total_active_blocks'] ?? 0) }}</h3>
                    </div>
                    <div class="security-metric-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-shield-slash"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-6">
            <div class="card security-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="metric-label">Permanent Blocks</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1" id="stat-permanent-blocks">{{ number_format($stats['permanent_blocks'] ?? 0) }}</h3>
                    </div>
                    <div class="security-metric-icon bg-dark bg-opacity-10 text-dark">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-6">
            <div class="card security-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="metric-label">Temporary Blocks</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1" id="stat-temporary-blocks">{{ number_format($stats['temporary_blocks'] ?? 0) }}</h3>
                    </div>
                    <div class="security-metric-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-6">
            <div class="card security-metric-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="metric-label">Blocked Networks</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1" id="stat-blocked-networks">{{ number_format($stats['total_blocked_networks'] ?? 0) }}</h3>
                    </div>
                    <div class="security-metric-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-sitemap"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="card security-filter-card mb-4">
        <div class="card-body">
            <form id="filterBlockedIpsForm" class="row g-2 align-items-end">

                <div class="col-xl-4 col-md-5 col-12">
                    <label class="form-label">Search IP or Reason</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control form-control-sm font-monospace" id="filter_search" name="search" placeholder="IP, scanner, bot..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="filter_status" name="status">
                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Expired / Inactive</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Records</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label">Block Type</label>
                    <select class="form-select form-select-sm" id="filter_type" name="block_type">
                        <option value="">All Types</option>
                        <option value="permanent" {{ request('block_type') === 'permanent' ? 'selected' : '' }}>Permanent</option>
                        <option value="temporary" {{ request('block_type') === 'temporary' ? 'selected' : '' }}>Temporary</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label">Category</label>
                    <select class="form-select form-select-sm" id="filter_category" name="category">
                        <option value="">All Categories</option>
                        <option value="manual" {{ request('category') === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="multiple_orders" {{ request('category') === 'multiple_orders' ? 'selected' : '' }}>Multiple Orders</option>
                        <option value="spam_form" {{ request('category') === 'spam_form' ? 'selected' : '' }}>Spam Forms</option>
                        <option value="brute_force" {{ request('category') === 'brute_force' ? 'selected' : '' }}>Brute Force</option>
                        <option value="url_attack" {{ request('category') === 'url_attack' ? 'selected' : '' }}>URL Probing</option>
                    </select>
                </div>

                <div class="col-xl-1 col-md-2 col-6">
                    <label class="form-label">Per Page</label>
                    <select class="form-select form-select-sm" id="filter_per_page" name="per_page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-xl-1 col-md-2 col-12">
                    <label class="form-label d-none d-xl-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-light border w-100" id="btn-reset-filters">
                        <i class="fas fa-rotate-left me-1"></i>
                        Reset
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Blocked IP Table --}}
    <div class="card security-table-card">

        <div class="table-responsive">
            <table class="table table-hover align-middle security-table">

                <thead>
                    <tr>
                        <th class="ps-3" style="width: 200px;">IP Address</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Reason &amp; Notes</th>
                        <th>Blocked By</th>
                        <th>Expires</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="table-blocked-ips-body">
                    @include('admin.security.blocking.partials.blocked_ip_rows', ['blockedIps' => $blockedIps])
                </tbody>

            </table>
        </div>

        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="small text-muted" id="showing-entries-text">
                Showing <strong>{{ $blockedIps->firstItem() ?? 0 }}</strong> to <strong>{{ $blockedIps->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($blockedIps->total()) }}</strong> entries
            </span>

            <div id="pagination-container">
                {{ $blockedIps->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>

</div>

{{-- Modal: Add Blocked IP --}}
<div class="modal fade" id="modalAddBlockedIp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">

            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="fas fa-ban me-2"></i>
                    Block IP Address
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formStoreBlockedIp">
                @csrf

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            IP Address <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm font-monospace" name="ip_address" required placeholder="103.204.120.50">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">Block Type</label>
                            <select class="form-select form-select-sm" name="block_type" id="modal_block_type" required>
                                <option value="temporary" selected>Temporary</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>

                        <div class="col-sm-6" id="modal_duration_wrapper">
                            <label class="form-label">Duration</label>
                            <select class="form-select form-select-sm" name="duration_minutes">
                                <option value="60">1 Hour</option>
                                <option value="120" selected>2 Hours</option>
                                <option value="360">6 Hours</option>
                                <option value="1440">24 Hours</option>
                                <option value="10080">7 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select form-select-sm" name="block_category">
                            <option value="manual" selected>Manual</option>
                            <option value="multiple_orders">Multiple Fraud Orders</option>
                            <option value="spam_form">Spam Forms</option>
                            <option value="brute_force">Brute Force</option>
                            <option value="url_attack">URL Probing</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Reason <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm" name="block_reason" required placeholder="Enter reason for blocking...">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Internal Note (Optional)</label>
                        <textarea class="form-control form-control-sm" name="admin_note" rows="2" placeholder="Add internal notes..."></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-block">Block IP</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal: Emergency Flush --}}
<div class="modal fade" id="modalEmergencyFlush" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow">

            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-triangle-exclamation me-2"></i>
                    Emergency Flush
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEmergencyFlush">
                @csrf

                <div class="modal-body">

                    <p class="small text-muted mb-3">
                        This will <strong>deactivate all active IP blocks immediately</strong>. Use only when necessary.
                    </p>

                    <label class="form-label mb-2">
                        Type <code>CONFIRM_FLUSH</code> to proceed:
                    </label>

                    <input type="text" class="form-control form-control-sm font-monospace text-center" id="flush_confirmation" required placeholder="CONFIRM_FLUSH">

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger" id="btn-execute-flush">Flush All Blocks</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
(function() {
    const csrfToken = '{{ csrf_token() }}';
    const filterForm = document.getElementById('filterBlockedIpsForm');
    const tbody = document.getElementById('table-blocked-ips-body');
    const paginationContainer = document.getElementById('pagination-container');
    const showingText = document.getElementById('showing-entries-text');
    let searchTimer = null;

    function fetchBlockedIps(targetUrl = null) {
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const url = targetUrl || `{{ route('admin.security.blocking.ips') }}?${params.toString()}`;

        if (tbody) tbody.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tbody) tbody.style.opacity = '1';

            if (res.success) {
                if (tbody) tbody.innerHTML = res.html;
                if (paginationContainer && res.pagination) paginationContainer.innerHTML = res.pagination;

                if (showingText && res.total !== undefined) {
                    showingText.innerHTML = `Showing results of <strong>${Number(res.total).toLocaleString()}</strong> entries`;
                }
            }
        })
        .catch(err => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tbody) tbody.style.opacity = '1';
            console.error('Fetch error:', err);
        });
    }

    function initEvents() {

        // Search
        const searchInput = document.getElementById('filter_search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => fetchBlockedIps(), 350);
            });
        }

        // Filters
        document.querySelectorAll('#filter_status, #filter_type, #filter_category, #filter_per_page').forEach(el => {
            el.addEventListener('change', () => fetchBlockedIps());
        });

        // Reset
        const resetBtn = document.getElementById('btn-reset-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterForm.reset();
                fetchBlockedIps(`{{ route('admin.security.blocking.ips') }}`);
            });
        }

        // Pagination
        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#pagination-container .page-link');
            if (pageLink && pageLink.getAttribute('href')) {
                e.preventDefault();
                fetchBlockedIps(pageLink.getAttribute('href'));
            }
        });

        // Refresh
        const refreshBtn = document.getElementById('btn-refresh-blocked-ips');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => fetchBlockedIps());
        }

        // Block type toggle
        const modalBlockType = document.getElementById('modal_block_type');
        const modalDurWrap = document.getElementById('modal_duration_wrapper');

        if (modalBlockType && modalDurWrap) {
            modalBlockType.addEventListener('change', function() {
                modalDurWrap.style.display = this.value === 'permanent' ? 'none' : 'block';
            });
        }

        // Add IP Block
        const addForm = document.getElementById('formStoreBlockedIp');

        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const btn = document.getElementById('btn-submit-block');
                btn.disabled = true;
                btn.innerText = 'Blocking...';

                const formData = new FormData(addForm);

                fetch(`{{ route('admin.security.blocking.ips.store') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerText = 'Block IP';

                    alert(res.message);

                    if (res.success) {
                        addForm.reset();

                        const modalEl = document.getElementById('modalAddBlockedIp');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }

                        fetchBlockedIps();
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerText = 'Block IP';
                    alert('Unable to create block entry.');
                });
            });
        }

        // Quick Unblock
        document.addEventListener('click', function(e) {
            const unblockBtn = e.target.closest('.btn-ajax-unblock');

            if (unblockBtn) {
                const id = unblockBtn.getAttribute('data-id');
                const ip = unblockBtn.getAttribute('data-ip');

                if (confirm(`Unblock IP: ${ip}?`)) {
                    unblockBtn.disabled = true;
                    unblockBtn.innerText = '...';

                    fetch(`{{ url('/admin/security/blocking/ips/unblock') }}/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            note: 'Unblocked from Blocked IPs Manager'
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        alert(res.message);

                        if (res.success) {
                            const row = document.getElementById(`row-blocked-ip-${id}`);
                            if (row) row.remove();
                            fetchBlockedIps();
                        } else {
                            unblockBtn.disabled = false;
                            unblockBtn.innerText = 'Unblock';
                        }
                    })
                    .catch(() => {
                        unblockBtn.disabled = false;
                        unblockBtn.innerText = 'Unblock';
                    });
                }
            }
        });

        // Emergency Flush
        const flushForm = document.getElementById('formEmergencyFlush');

        if (flushForm) {
            flushForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const code = document.getElementById('flush_confirmation').value.trim();

                if (code !== 'CONFIRM_FLUSH') {
                    alert('Enter CONFIRM_FLUSH exactly.');
                    return;
                }

                const btn = document.getElementById('btn-execute-flush');
                btn.disabled = true;
                btn.innerText = 'Flushing...';

                fetch(`{{ route('admin.security.blocking.emergency_flush') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        confirmation: code
                    })
                })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerText = 'Flush All Blocks';

                    alert(res.message);

                    if (res.success) {
                        const modalEl = document.getElementById('modalEmergencyFlush');

                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }

                        fetchBlockedIps();
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = 'Flush All Blocks';
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEvents);
    } else {
        initEvents();
    }
})();
</script>

@endsection
