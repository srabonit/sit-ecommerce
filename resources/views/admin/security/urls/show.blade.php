@extends('layouts.admin')

@section('title', 'URL Forensic Audit: ' . $urlAttack->url_path)

@section('admin_content')
@php
    $totalRequests = (int) ($urlAttack->total_requests ?? 0);
    $failedRequests = (int) ($urlAttack->failed_requests ?? 0);
    $blockedRequests = (int) ($urlAttack->blocked_requests ?? 0);
    $uniqueIps = (int) ($urlAttack->unique_ips_count ?? 0);
@endphp

<style>
    /* =========================================================
       URL FORENSIC AUDIT — Kaiadmin Classic / White
       Scoped styles only; global admin theme remains untouched.
       ========================================================= */
    #security-url-details-wrapper,
    #security-url-details-wrapper *,
    #modalBulkBlockEndpointIps,
    #modalBulkBlockEndpointIps * {
        box-sizing: border-box;
    }

    #security-url-details-wrapper {
        --url-audit-radius: 5px;
        --url-audit-border: #e8edf3;
        --url-audit-muted: #788495;
        --url-audit-heading: #263445;
        --url-audit-surface: #ffffff;
        --url-audit-soft: #f6f8fb;
        color: var(--url-audit-heading);
        font-size: 13px;
        min-width: 0;
    }

    #security-url-details-wrapper .card,
    #modalBulkBlockEndpointIps .modal-content,
    #security-url-details-wrapper .btn,
    #security-url-details-wrapper .form-control,
    #security-url-details-wrapper .form-select,
    #modalBulkBlockEndpointIps .btn,
    #modalBulkBlockEndpointIps .form-control,
    #modalBulkBlockEndpointIps .form-select,
    #security-url-details-wrapper .badge {
        border-radius: var(--url-audit-radius) !important;
    }

    #security-url-details-wrapper .card {
        border: 1px solid var(--url-audit-border) !important;
        box-shadow: 0 2px 9px rgba(31, 45, 61, .045) !important;
        background: var(--url-audit-surface);
    }

    #security-url-details-wrapper .audit-page-header {
        border-bottom: 1px solid var(--url-audit-border) !important;
        padding-bottom: 14px;
    }

    #security-url-details-wrapper .audit-title {
        color: var(--url-audit-heading);
        font-size: 18px;
        font-weight: 750;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #security-url-details-wrapper .audit-subtitle {
        color: var(--url-audit-muted);
        font-size: 11.5px;
        line-height: 1.55;
    }

    #security-url-details-wrapper .audit-method {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
    }

    #security-url-details-wrapper .audit-action-group {
        gap: 7px !important;
    }

    #security-url-details-wrapper .btn {
        font-size: 11.5px;
        font-weight: 600;
        min-height: 32px;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #security-url-details-wrapper .btn-sm {
        padding: 6px 10px;
    }

    #security-url-details-wrapper .btn:focus,
    #security-url-details-wrapper .form-control:focus,
    #security-url-details-wrapper .form-select:focus {
        box-shadow: 0 0 0 .15rem rgba(var(--bs-primary-rgb, 13, 110, 253), .12);
    }

    #security-url-details-wrapper .audit-metric-card {
        position: relative;
        overflow: hidden;
        min-height: 112px;
        padding: 15px !important;
    }

    #security-url-details-wrapper .audit-metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    #security-url-details-wrapper .audit-metric-label {
        color: var(--url-audit-muted);
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .055em;
        line-height: 1.45;
        text-transform: uppercase;
    }

    #security-url-details-wrapper .audit-metric-icon {
        align-items: center;
        background: var(--url-audit-soft);
        border: 1px solid var(--url-audit-border);
        border-radius: 5px;
        display: inline-flex;
        flex: 0 0 32px;
        height: 32px;
        justify-content: center;
        width: 32px;
    }

    #security-url-details-wrapper .audit-metric-value {
        font-size: 24px;
        font-weight: 750;
        letter-spacing: -.035em;
        line-height: 1.2;
        margin: 8px 0 3px;
    }

    #security-url-details-wrapper .audit-metric-note {
        color: var(--url-audit-muted);
        display: block;
        font-size: 10.5px;
        line-height: 1.4;
    }

    #security-url-details-wrapper .audit-section-card {
        overflow: hidden;
    }

    #security-url-details-wrapper .audit-card-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid var(--url-audit-border);
        display: flex;
        gap: 12px;
        justify-content: space-between;
        min-height: 58px;
        padding: 12px 15px;
    }

    #security-url-details-wrapper .audit-section-title {
        color: var(--url-audit-heading);
        font-size: 12.5px;
        font-weight: 750;
        line-height: 1.45;
        margin: 0;
    }

    #security-url-details-wrapper .audit-section-note {
        color: var(--url-audit-muted);
        display: block;
        font-size: 10.5px;
        line-height: 1.4;
        margin-top: 3px;
    }

    #security-url-details-wrapper .table-responsive {
        scrollbar-color: #c8d0da #f4f6f8;
        scrollbar-width: thin;
        width: 100%;
    }

    #security-url-details-wrapper .table {
        margin-bottom: 0;
        min-width: 850px;
        vertical-align: middle;
    }

    #security-url-details-wrapper .table thead th {
        background: #f7f9fc;
        border-bottom: 1px solid var(--url-audit-border);
        color: #687587;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .025em;
        padding: 10px 9px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #security-url-details-wrapper .table tbody td {
        border-color: #edf0f4;
        color: #536173;
        font-size: 11.5px;
        padding: 10px 9px;
    }

    #security-url-details-wrapper .table tbody tr:last-child td {
        border-bottom: 0;
    }

    #security-url-details-wrapper .table-hover tbody tr:hover > * {
        --bs-table-bg-state: #f8faff;
    }

    #security-url-details-wrapper .table .font-monospace {
        font-size: 11.5px;
    }

    #security-url-details-wrapper .hover-primary:hover {
        color: var(--bs-primary) !important;
    }

    #security-url-details-wrapper .audit-risk-badge,
    #security-url-details-wrapper .audit-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        line-height: 1.35;
    }

    #security-url-details-wrapper .audit-payload-card {
        border-left: 3px solid var(--bs-danger) !important;
    }

    #security-url-details-wrapper .audit-payload {
        background: #202733;
        border: 1px solid #303b4a;
        border-radius: 5px;
        color: #ff8c96;
        font-size: 11.5px;
        line-height: 1.6;
        overflow-wrap: anywhere;
        padding: 11px 12px;
        white-space: pre-wrap;
    }

    #security-url-details-wrapper .audit-empty {
        color: var(--url-audit-muted) !important;
        font-size: 11.5px !important;
        padding: 24px 12px !important;
        text-align: center;
    }

    #security-url-details-wrapper .audit-count-badge {
        background: #f5f7fa;
        border: 1px solid var(--url-audit-border);
        color: #596779;
        font-size: 10px;
        font-weight: 650;
        white-space: nowrap;
    }

    #modalBulkBlockEndpointIps .modal-content {
        border: 1px solid var(--url-audit-border);
        overflow: hidden;
    }

    #modalBulkBlockEndpointIps .modal-header,
    #modalBulkBlockEndpointIps .modal-footer {
        border-color: var(--url-audit-border);
    }

    #modalBulkBlockEndpointIps .modal-title {
        font-size: 13px;
    }

    #modalBulkBlockEndpointIps .form-label {
        font-size: 11px;
        margin-bottom: 5px;
    }

    #modalBulkBlockEndpointIps .form-control,
    #modalBulkBlockEndpointIps .form-select {
        font-size: 12px;
        min-height: 34px;
    }

    @media (max-width: 991.98px) {
        #security-url-details-wrapper {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        #security-url-details-wrapper .audit-page-header {
            align-items: flex-start !important;
        }

        #security-url-details-wrapper .audit-action-group {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        #security-url-details-wrapper {
            padding: 12px !important;
        }

        #security-url-details-wrapper .audit-page-header {
            gap: 12px !important;
            margin-bottom: 15px !important;
        }

        #security-url-details-wrapper .audit-heading-row {
            align-items: flex-start !important;
            flex-wrap: wrap;
        }

        #security-url-details-wrapper .audit-title {
            font-size: 15px;
        }

        #security-url-details-wrapper .audit-subtitle {
            font-size: 10.5px;
            margin-top: 6px !important;
        }

        #security-url-details-wrapper .audit-action-group {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
        }

        #security-url-details-wrapper .audit-action-group .btn {
            justify-content: center;
            white-space: normal;
            min-height: 36px;
        }

        #security-url-details-wrapper .audit-action-group .btn-danger {
            grid-column: 1 / -1;
        }

        #security-url-details-wrapper .audit-metric-card {
            min-height: 104px;
            padding: 12px !important;
        }

        #security-url-details-wrapper .audit-metric-value {
            font-size: 21px;
        }

        #security-url-details-wrapper .audit-card-header {
            align-items: flex-start;
            padding: 11px 12px;
        }

        #security-url-details-wrapper .audit-section-title {
            font-size: 11.5px;
        }

        #security-url-details-wrapper .audit-count-badge {
            font-size: 9px;
        }

        #modalBulkBlockEndpointIps .modal-dialog {
            margin: 10px;
        }
    }
</style>

<div class="container-fluid px-4 py-3" id="security-url-details-wrapper">
    <!-- Top Header & Navigation Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 audit-page-header">
        <div class="min-w-0 flex-grow-1">
            <div class="d-flex align-items-center gap-2 audit-heading-row">
                <a href="{{ route('admin.security.urls.index') }}" class="btn btn-sm btn-light border text-secondary shadow-sm flex-shrink-0" title="Back to Attacked URLs">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 font-monospace audit-method flex-shrink-0">
                    {{ strtoupper($urlAttack->request_method ?? 'GET') }}
                </span>
                <h4 class="audit-title mb-0 d-flex align-items-center font-monospace">
                    <i class="fas fa-bullseye text-danger me-2 flex-shrink-0"></i>
                    <span>{{ $urlAttack->url_path }}</span>
                </h4>
            </div>
            <p class="audit-subtitle mb-0 mt-2">
                Forensic investigation of targeted endpoint hits, malicious scanning traffic, and attacker source networks
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap align-items-center audit-action-group">
            <!-- Bulk Block Attacking IPs -->
            <button type="button"
                    class="btn btn-sm btn-danger shadow-sm px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalBulkBlockEndpointIps"
                    {{ $uniqueIps <= 0 ? 'disabled' : '' }}>
                <i class="fas fa-shield-virus me-1"></i> Block All Attacking IPs ({{ $uniqueIps }})
            </button>

            <!-- Reset Counter -->
            <button type="button" class="btn btn-sm btn-light border shadow-sm text-secondary" id="btn-reset-this-url" data-id="{{ $urlAttack->id }}">
                <i class="fas fa-rotate-left me-1"></i> Reset Metrics
            </button>

            <a href="{{ route('admin.security.urls.index') }}" class="btn btn-sm btn-light border text-secondary shadow-sm">
                <i class="fas fa-list me-1"></i> All Targeted Routes
            </a>
        </div>
    </div>

    <!-- FORENSIC METRIC CARDS -->
    <div class="row g-3 mb-4">
        <!-- Total Requests -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card audit-metric-card h-100">
                <div class="audit-metric-top">
                    <span class="audit-metric-label">Total Endpoint Hits</span>
                    <span class="audit-metric-icon text-primary"><i class="fas fa-chart-line"></i></span>
                </div>
                <h3 class="audit-metric-value text-dark font-monospace">{{ number_format($totalRequests) }}</h3>
                <small class="audit-metric-note">Aggregated request volume</small>
            </div>
        </div>

        <!-- Failed Requests (4xx) -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card audit-metric-card h-100">
                <div class="audit-metric-top">
                    <span class="audit-metric-label">Failed / Probed Hits (4xx)</span>
                    <span class="audit-metric-icon text-warning"><i class="fas fa-triangle-exclamation"></i></span>
                </div>
                <h3 class="audit-metric-value text-warning font-monospace">{{ number_format($failedRequests) }}</h3>
                <small class="audit-metric-note">Scanner reconnaissance</small>
            </div>
        </div>

        <!-- Blocked Requests (403) -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card audit-metric-card h-100">
                <div class="audit-metric-top">
                    <span class="audit-metric-label">Firewall Blocked Hits</span>
                    <span class="audit-metric-icon text-danger"><i class="fas fa-shield-halved"></i></span>
                </div>
                <h3 class="audit-metric-value text-danger font-monospace">{{ number_format($blockedRequests) }}</h3>
                <small class="audit-metric-note">Intercepted before execution</small>
            </div>
        </div>

        <!-- Unique Attacking IPs -->
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card audit-metric-card h-100">
                <div class="audit-metric-top">
                    <span class="audit-metric-label">Distinct Attacking IPs</span>
                    <span class="audit-metric-icon text-primary"><i class="fas fa-network-wired"></i></span>
                </div>
                <h3 class="audit-metric-value text-primary font-monospace">{{ number_format($uniqueIps) }}</h3>
                <small class="audit-metric-note">Unique origin sources</small>
            </div>
        </div>
    </div>

    <!-- SAMPLE SUSPICIOUS PAYLOAD CARD (IF DETECTED) -->
    @if(!empty($urlAttack->sample_suspicious_payload))
        <div class="card audit-payload-card mb-4 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <span class="fw-bold text-danger small">
                        <i class="fas fa-bug me-1"></i> Malicious Payload / Injection String Captured:
                    </span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 10px;">
                        Anomaly Detected
                    </span>
                </div>
                <div class="audit-payload font-monospace text-break">{{ $urlAttack->sample_suspicious_payload }}</div>
                <small class="text-muted mt-2 d-block" style="font-size: 10.5px; line-height: 1.5;">
                    This query parameter matches signatures commonly found in SQL Injections, Remote Code Execution (RCE), or Path Traversal vulnerability scanners.
                </small>
            </div>
        </div>
    @endif

    <!-- ATTACKING IP ADDRESSES AGGREGATION TABLE -->
    <div class="card audit-section-card mb-4 bg-white">
        <div class="audit-card-header">
            <div class="min-w-0">
                <h6 class="audit-section-title">
                    <i class="fas fa-network-wired text-primary me-2"></i> Attacking IP Addresses targeting this Route (Top 15)
                </h6>
                <small class="audit-section-note">Ranked by total attack request frequency</small>
            </div>
            <span class="badge audit-count-badge px-2 py-1">{{ $attackingIps->count() }} IPs Recorded</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 180px;">Client IP</th>
                        <th>Country</th>
                        <th>Device / Platform</th>
                        <th class="text-center">Hit Count</th>
                        <th class="text-center">Risk Score</th>
                        <th>Last Attempt</th>
                        <th>Status</th>
                        <th class="pe-3 text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attackingIps as $ipRow)
                        @php
                            $isBlocked = $ipRow->is_blocked ?? false;
                            $risk = (int) ($ipRow->max_risk ?? 0);
                            $riskBadge = match(true) {
                                $risk >= 75 => 'bg-danger text-white',
                                $risk >= 40 => 'bg-warning text-dark',
                                default     => 'bg-success text-white',
                            };
                        @endphp
                        <tr class="{{ $isBlocked ? 'table-danger bg-opacity-25' : '' }}">
                            <td class="ps-3 font-monospace fw-bold text-dark">
                                <a href="{{ route('admin.security.ips.show', $ipRow->ip_address) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $ipRow->ip_address }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="fas fa-globe text-secondary me-1"></i> {{ $ipRow->country ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $ipRow->device_type ?? 'Desktop' }} ({{ $ipRow->browser ?? 'Browser' }})
                            </td>
                            <td class="text-center fw-bold font-monospace">{{ number_format((int) ($ipRow->hits_count ?? 0)) }}</td>
                            <td class="text-center">
                                <span class="badge audit-risk-badge {{ $riskBadge }} font-monospace px-2 py-1" style="font-size: 10px;">
                                    {{ $risk }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ !empty($ipRow->last_hit) ? \Carbon\Carbon::parse($ipRow->last_hit)->diffForHumans() : 'N/A' }}
                            </td>
                            <td>
                                @if($isBlocked)
                                    <span class="badge audit-status-badge bg-danger px-2 py-1" style="font-size: 9.5px;">Blocked</span>
                                @else
                                    <span class="badge audit-status-badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 9.5px;">Active</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.security.ips.show', $ipRow->ip_address) }}" class="btn btn-outline-secondary" title="View Full IP Activity">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$isBlocked)
                                        <button type="button" class="btn btn-outline-danger btn-quick-block-ip" data-ip="{{ $ipRow->ip_address }}" title="Block this IP">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="audit-empty">No attacker IP records found for this URL.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECENT REQUEST LOG TIMELINE ON THIS ENDPOINT (LAST 25) -->
    <div class="card audit-section-card bg-white mb-4">
        <div class="audit-card-header">
            <div>
                <h6 class="audit-section-title">
                    <i class="fas fa-timeline text-primary me-2"></i> Recent Request Sequence (Last 25 Events)
                </h6>
                <small class="audit-section-note">Chronological request activity captured for this endpoint</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 140px;">Timestamp</th>
                        <th>Client IP</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                        <th>Activity Type</th>
                        <th class="text-center">Risk</th>
                        <th class="pe-3">User Agent Snippet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr class="{{ ($log->security_status ?? '') === 'blocked' ? 'table-danger bg-opacity-25' : '' }}">
                            <td class="ps-3 font-monospace text-muted" style="font-size: 10.5px;">
                                {{ $log->created_at ? $log->created_at->format('H:i:s') : 'N/A' }}
                                <span class="d-block text-secondary" style="font-size: 9.5px;">{{ $log->created_at ? $log->created_at->format('Y-m-d') : 'N/A' }}</span>
                            </td>
                            <td class="font-monospace fw-semibold">
                                <a href="{{ route('admin.security.ips.show', $log->ip_address) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $log->ip_address }}
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2 py-1" style="font-size: 9.5px;">{{ $log->request_method ?? 'GET' }}</span></td>
                            <td class="text-center">
                                @php
                                    $st = (int) ($log->response_status ?? 200);
                                    $stClass = match(true) {
                                        $st >= 200 && $st < 300 => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                        $st === 403 || $st === 429 => 'bg-danger text-white',
                                        $st >= 400 => 'bg-warning text-dark',
                                        default => 'bg-light text-dark border',
                                    };
                                @endphp
                                <span class="badge {{ $stClass }} px-2 py-1" style="font-size: 10px;">{{ $st }}</span>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1" style="font-size: 10px;">{{ $log->activity_type ?? 'traffic' }}</span></td>
                            <td class="text-center font-monospace">{{ $log->risk_score ?: 0 }}</td>
                            <td class="pe-3 text-muted small text-truncate" style="max-width: 250px;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="audit-empty">No forensic logs recorded for this endpoint yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: BULK BLOCK ALL ATTACKING IPS FOR THIS ENDPOINT -->
<div class="modal fade" id="modalBulkBlockEndpointIps" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="fas fa-shield-virus me-2"></i> Bulk Block All Attacking IPs
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitBulkBlockDetail">
                <div class="modal-body p-3">
                    <div class="alert alert-warning border-0 small py-2 px-3 mb-3">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        This action will block all <strong>{{ $uniqueIps }} distinct IPs</strong> that targeted <code>{{ $urlAttack->url_path }}</code>.
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Block Type</label>
                            <select class="form-select form-select-sm" id="detail_bulk_block_type" name="block_type" required>
                                <option value="temporary">Temporary Restriction</option>
                                <option value="permanent">Permanent Restriction</option>
                            </select>
                        </div>
                        <div class="col-sm-6" id="detail_bulk_duration_wrapper">
                            <label class="form-label small fw-semibold text-secondary">Duration</label>
                            <select class="form-select form-select-sm" name="duration_minutes">
                                <option value="60">1 Hour</option>
                                <option value="120" selected>2 Hours</option>
                                <option value="360">6 Hours</option>
                                <option value="1440">24 Hours (1 Day)</option>
                                <option value="10080">7 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Block Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="reason" required value="Coordinated malicious probing on route: {{ htmlspecialchars($urlAttack->url_path, ENT_QUOTES, 'UTF-8') }}">
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-detail-bulk-block">Enforce Bulk Block</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlTargetEndpoint = @json($urlAttack->url_path);

    // ১. বাল্ক ব্লক টাইপ টগল
    const blockType = document.getElementById('detail_bulk_block_type');
    const durWrap = document.getElementById('detail_bulk_duration_wrapper');
    if (blockType && durWrap) {
        blockType.addEventListener('change', function() {
            durWrap.style.display = this.value === 'permanent' ? 'none' : 'block';
        });
    }

    // ২. বাল্ক ব্লক সাবমিট
    const bulkForm = document.getElementById('formSubmitBulkBlockDetail');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-detail-bulk-block');
            btn.disabled = true;
            btn.innerText = 'Enforcing...';

            const payload = {
                block_type: bulkForm.querySelector('[name="block_type"]').value,
                duration_minutes: bulkForm.querySelector('[name="duration_minutes"]').value,
                reason: bulkForm.querySelector('[name="reason"]').value,
            };

            fetch(`{{ route('admin.security.urls.block_all', $urlAttack->id) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                alert(res.message);
                if (res.success) location.reload();
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Enforce Bulk Block';
            });
        });
    }

    // ৩. রুট মেট্রিক্স রিসেট
    const resetBtn = document.getElementById('btn-reset-this-url');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to reset metric counters for this URL?')) {
                fetch(`{{ route('admin.security.urls.reset', $urlAttack->id) }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    alert(res.message);
                    if (res.success) window.location.href = `{{ route('admin.security.urls.index') }}`;
                });
            }
        });
    }

    // ৪. একক আক্রমণকারী আইপি ব্লক (নিরাপদ স্ট্রিং কনক্যাটেনেশন)
    document.querySelectorAll('.btn-quick-block-ip').forEach(btn => {
        btn.addEventListener('click', function() {
            const ip = this.getAttribute('data-ip');
            if (confirm(`Block IP address: ${ip}?`)) {
                fetch(`{{ route('admin.security.ips.block') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ip_address: ip,
                        block_type: 'temporary',
                        duration_minutes: 120,
                        block_reason: 'Targeted scanning on ' + urlTargetEndpoint
                    })
                })
                .then(r => r.json())
                .then(res => {
                    alert(res.message);
                    if (res.success) location.reload();
                });
            }
        });
    });
});
</script>
@endpush
