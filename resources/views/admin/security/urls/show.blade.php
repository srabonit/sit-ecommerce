@extends('layouts.admin')

@section('title', 'URL Forensic Audit: ' . $urlAttack->url_path)

@section('admin_content')
@php
    $totalRequests = (int) ($urlAttack->total_requests ?? 0);
    $failedRequests = (int) ($urlAttack->failed_requests ?? 0);
    $blockedRequests = (int) ($urlAttack->blocked_requests ?? 0);
    $uniqueIps = (int) ($urlAttack->unique_ips_count ?? 0);
@endphp

<div class="container-fluid px-4 py-3" id="security-url-details-wrapper">
    <!-- Top Header & Navigation Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.security.urls.index') }}" class="btn btn-sm btn-light border text-secondary shadow-sm" title="Back to Attacked URLs">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 font-monospace" style="font-size: 11px;">
                    {{ strtoupper($urlAttack->request_method ?? 'GET') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center font-monospace text-break">
                    <i class="fas fa-bullseye text-danger me-2"></i> {{ $urlAttack->url_path }}
                </h4>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Forensic investigation of targeted endpoint hits, malicious scanning traffic, and attacker source networks
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap align-items-center gap-2">
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
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 10.5px;">Total Endpoint Hits</span>
                <h3 class="fw-bold mb-0 text-dark mt-1 font-monospace">{{ number_format($totalRequests) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Aggregated request volume</small>
            </div>
        </div>

        <!-- Failed Requests (4xx) -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 10.5px;">Failed / Probed Hits (4xx)</span>
                <h3 class="fw-bold mb-0 text-warning mt-1 font-monospace">{{ number_format($failedRequests) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Scanner reconnaissance</small>
            </div>
        </div>

        <!-- Blocked Requests (403) -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 10.5px;">Firewall Blocked Hits</span>
                <h3 class="fw-bold mb-0 text-danger mt-1 font-monospace">{{ number_format($blockedRequests) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Intercepted before execution</small>
            </div>
        </div>

        <!-- Unique Attacking IPs -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 10.5px;">Distinct Attacking IPs</span>
                <h3 class="fw-bold mb-0 text-primary mt-1 font-monospace">{{ number_format($uniqueIps) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Unique origin sources</small>
            </div>
        </div>
    </div>

    <!-- SAMPLE SUSPICIOUS PAYLOAD CARD (IF DETECTED) -->
    @if(!empty($urlAttack->sample_suspicious_payload))
        <div class="card border-0 shadow-sm rounded-3 mb-4 border-start border-danger border-4 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-danger small">
                        <i class="fas fa-bug me-1"></i> Malicious Payload / Injection String Captured:
                    </span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5" style="font-size: 10px;">
                        Anomaly Detected
                    </span>
                </div>
                <div class="bg-dark text-danger p-2.5 rounded-2 font-monospace text-break small">
                    {{ $urlAttack->sample_suspicious_payload }}
                </div>
                <small class="text-muted mt-2 d-block" style="font-size: 11px;">
                    This query parameter matches signatures commonly found in SQL Injections, Remote Code Execution (RCE), or Path Traversal vulnerability scanners.
                </small>
            </div>
        </div>
    @endif

    <!-- ATTACKING IP ADDRESSES AGGREGATION TABLE -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center justify-content-between">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-network-wired text-primary me-2"></i> Attacking IP Addresses targeting this Route (Top 15)
                </h6>
                <small class="text-muted" style="font-size: 11px;">Ranked by total attack request frequency</small>
            </div>
            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">
                {{ $attackingIps->count() }} IPs Recorded
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light text-muted border-bottom" style="font-size: 11px;">
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
                                <span class="badge bg-light text-dark border px-2 py-0.5">
                                    <i class="fas fa-globe text-secondary me-1"></i> {{ $ipRow->country ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $ipRow->device_type ?? 'Desktop' }} ({{ $ipRow->browser ?? 'Browser' }})
                            </td>
                            <td class="text-center fw-bold font-monospace">{{ number_format((int) ($ipRow->hits_count ?? 0)) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $riskBadge }} font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                                    {{ $risk }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ !empty($ipRow->last_hit) ? \Carbon\Carbon::parse($ipRow->last_hit)->diffForHumans() : 'N/A' }}
                            </td>
                            <td>
                                @if($isBlocked)
                                    <span class="badge bg-danger px-1.5 py-0.5" style="font-size: 9.5px;">Blocked</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-1.5 py-0.5" style="font-size: 9.5px;">Active</span>
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
                            <td colspan="8" class="text-center py-4 text-muted small">No attacker IP records found for this URL.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECENT REQUEST LOG TIMELINE ON THIS ENDPOINT (LAST 25) -->
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-timeline text-primary me-2"></i> Recent Request Sequence (Last 25 Events)
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                <thead class="table-light text-muted" style="font-size: 11px;">
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
                            <td class="ps-3 font-monospace text-muted" style="font-size: 11px;">
                                {{ $log->created_at ? $log->created_at->format('H:i:s') : 'N/A' }}
                                <span class="d-block text-secondary" style="font-size: 10px;">{{ $log->created_at ? $log->created_at->format('Y-m-d') : 'N/A' }}</span>
                            </td>
                            <td class="font-monospace fw-semibold">
                                <a href="{{ route('admin.security.ips.show', $log->ip_address) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $log->ip_address }}
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border px-1.5 py-0.5" style="font-size: 9.5px;">{{ $log->request_method ?? 'GET' }}</span></td>
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
                                <span class="badge {{ $stClass }} px-1.5 py-0.5" style="font-size: 10px;">{{ $st }}</span>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5" style="font-size: 10px;">{{ $log->activity_type ?? 'traffic' }}</span></td>
                            <td class="text-center font-monospace">{{ $log->risk_score ?: 0 }}</td>
                            <td class="pe-3 text-muted small text-truncate" style="max-width: 250px;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted small">No forensic logs recorded for this endpoint yet.</td>
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
            <div class="modal-header py-2.5 px-3 bg-light">
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