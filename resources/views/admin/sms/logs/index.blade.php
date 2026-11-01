@extends('layouts.admin')

@section('title', 'SMS Delivery Logs & DLR')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header & Export --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">SMS Logs</h4>
            <p class="text-muted small mb-0">Audit history, carrier delivery receipts (DLR), and gateway records.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.sms.logs.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Dispatched</span>
                    <h5 class="fw-bold text-dark mt-1 mb-0">{{ number_format($stats['total_sent'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Sent messages</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Delivered</span>
                    <h5 class="fw-bold text-success mt-1 mb-0">{{ number_format($stats['total_delivered'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Confirmed by carrier</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Failed / Blocked</span>
                    <h5 class="fw-bold text-danger mt-1 mb-0">{{ number_format(($stats['total_failed'] ?? 0) + ($stats['total_blocked'] ?? 0)) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Undelivered or restricted</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Total Spend</span>
                    <h5 class="fw-bold text-primary mt-1 mb-0">৳ {{ number_format((float) ($stats['total_cost'] ?? 0), 2) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Billed usage</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.logs.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Phone or message ID..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="sms_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="transactional" {{ request('sms_type') === 'transactional' ? 'selected' : '' }}>Transactional</option>
                        <option value="otp" {{ request('sms_type') === 'otp' ? 'selected' : '' }}>OTP Code</option>
                        <option value="marketing" {{ request('sms_type') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" title="Start Date">
                </div>

                <div class="col-6 col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" title="End Date">
                </div>

                <div class="col-12 col-md-1 text-end">
                    @if(request()->hasAny(['search', 'status', 'sms_type', 'start_date', 'end_date']))
                        <a href="{{ route('admin.sms.logs.index') }}" class="btn btn-light btn-sm px-2 text-muted" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-secondary btn-sm px-2">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                        <th class="ps-3 py-3">Recipient</th>
                        <th style="min-width: 260px;">Message Preview</th>
                        <th>Type</th>
                        <th>Credits / Cost</th>
                        <th>Status</th>
                        <th>Dispatched At</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($logs as $log)
                        <tr>
                            {{-- Recipient & Masking --}}
                            <td class="ps-3 py-3">
                                <div class="fw-semibold text-dark">{{ $log->recipient }}</div>
                                <div class="text-muted d-flex align-items-center gap-1" style="font-size: 11px;">
                                    <span>Mask: {{ $log->sender_id ?: 'Default' }}</span>
                                    @if($log->is_sandbox)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-0 px-1" style="font-size: 9px;">Sandbox</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Message Content --}}
                            <td>
                                <p class="text-muted mb-0 text-truncate" style="max-width: 320px;" title="{{ $log->message_content }}">
                                    {{ $log->message_content }}
                                </p>
                                @if($log->message_id)
                                    <code class="text-muted" style="font-size: 10px;">ID: {{ $log->message_id }}</code>
                                @endif
                            </td>

                            {{-- Type --}}
                            <td>
                                @php
                                    $typeBadges = [
                                        'transactional' => 'bg-info-subtle text-info border-info-subtle',
                                        'otp' => 'bg-primary-subtle text-primary border-primary-subtle',
                                        'marketing' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    ];
                                @endphp
                                <span class="badge border {{ $typeBadges[$log->sms_type] ?? 'bg-light text-secondary border' }}">
                                    {{ ucfirst($log->sms_type) }}
                                </span>
                            </td>

                            {{-- Credits & Cost --}}
                            <td>
                                <div class="fw-medium text-dark">{{ $log->sms_parts }} Part ({{ $log->characters_count }}c)</div>
                                <div class="text-muted" style="font-size: 11px;">৳ {{ number_format((float) $log->cost, 4) }}</div>
                            </td>

                            {{-- Status Badge --}}
                            <td>
                                @php
                                    $statusBadges = [
                                        'delivered' => 'bg-success-subtle text-success border-success-subtle',
                                        'sent' => 'bg-info-subtle text-info border-info-subtle',
                                        'failed' => 'bg-danger-subtle text-danger border-danger-subtle',
                                        'blocked' => 'bg-secondary-subtle text-secondary border',
                                    ];
                                @endphp
                                <span class="badge border {{ $statusBadges[$log->status] ?? 'bg-light text-muted border' }}" id="statusBadge-{{ $log->id }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>

                            {{-- Time --}}
                            <td>
                                <div class="text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $log->created_at->format('h:i:s A') }}</div>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    {{-- Inspection Modal Button --}}
                                    <button type="button" class="btn btn-light btn-inspect" data-id="{{ $log->id }}" data-url="{{ route('admin.sms.logs.show', $log->id) }}" title="Inspect Payload">
                                        <i class="fas fa-eye text-secondary"></i>
                                    </button>

                                    {{-- Check DLR Status --}}
                                    @if($log->message_id && $log->status === 'sent')
                                        <button type="button" class="btn btn-light btn-check-dlr" data-id="{{ $log->id }}" data-url="{{ route('admin.sms.logs.check_status', $log->id) }}" title="Sync DLR">
                                            <i class="fas fa-sync-alt text-primary" id="dlrIcon-{{ $log->id }}"></i>
                                        </button>
                                    @endif

                                    {{-- Resend Button --}}
                                    @if(in_array($log->status, ['failed', 'blocked']))
                                        <form action="{{ route('admin.sms.logs.resend', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Resend this message?');">
                                            @csrf
                                            <button type="submit" class="btn btn-light text-warning" title="Resend SMS">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-comment-slash fs-3 d-block mb-2 text-secondary"></i>
                                No SMS logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                    </span>
                    {{ $logs->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- Inspection Modal --}}
<div class="modal fade" id="logInspectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold" id="inspectModalTitle">SMS Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Recipient</span>
                        <strong class="text-dark small" id="mRecipient">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Gateway Status</span>
                        <strong class="small" id="mStatus">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Parts / Chars</span>
                        <strong class="text-dark small" id="mParts">-</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Total Cost</span>
                        <strong class="text-dark small" id="mCost">-</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Delivered Message</label>
                    <div class="p-3 bg-light rounded border text-dark font-monospace small" id="mBody" style="white-space: pre-wrap; word-break: break-word;"></div>
                </div>

                <div>
                    <label class="form-label small text-muted mb-1">Gateway API Response Payload</label>
                    <pre class="p-3 bg-dark text-light rounded small mb-0" id="mRaw" style="max-height: 180px; overflow-y: auto; font-size: 11px;"></pre>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
    @yield('admin_content')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('logInspectModal');
    let inspectModal = null;
    if (modalEl && typeof bootstrap !== 'undefined') {
        inspectModal = new bootstrap.Modal(modalEl);
    }

    // 1. Inspect Modal Fetch
    document.querySelectorAll('.btn-inspect').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');

            fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;
                    document.getElementById('inspectModalTitle').textContent = `Log #${data.id} - ${data.recipient}`;
                    document.getElementById('mRecipient').textContent = data.recipient;
                    document.getElementById('mStatus').textContent = (data.status || '').toUpperCase();
                    document.getElementById('mParts').textContent = `${data.sms_parts} Part(s) (${data.characters_count} chars)`;
                    document.getElementById('mCost').textContent = data.cost;
                    document.getElementById('mBody').textContent = data.message_content;
                    document.getElementById('mRaw').textContent = JSON.stringify(data.gateway_response, null, 2);

                    if (inspectModal) {
                        inspectModal.show();
                    }
                }
            })
            .catch(() => alert('Failed to retrieve log details.'));
        });
    });

    // 2. Immediate DLR Inquiry Check
    document.querySelectorAll('.btn-check-dlr').forEach(btn => {
        btn.addEventListener('click', function () {
            const logId = this.getAttribute('data-id');
            const url = this.getAttribute('data-url');
            const icon = document.getElementById(`dlrIcon-${logId}`);

            btn.disabled = true;
            if (icon) icon.classList.add('spin-animation');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById(`statusBadge-${logId}`);
                    if (badge) {
                        badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                        badge.className = data.status === 'delivered' 
                            ? 'badge border bg-success-subtle text-success border-success-subtle' 
                            : 'badge border bg-info-subtle text-info border-info-subtle';
                    }
                    btn.remove();
                } else {
                    alert(data.message || 'DLR inquiry failed.');
                }
            })
            .catch(() => alert('Network error during DLR inquiry.'))
            .finally(() => {
                btn.disabled = false;
                if (icon) icon.classList.remove('spin-animation');
            });
        });
    });
});
</script>
<style>
.spin-animation {
    display: inline-block;
    animation: rotate 1s linear infinite;
}
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush