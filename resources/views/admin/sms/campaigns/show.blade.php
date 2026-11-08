@extends('layouts.admin')

@section('title', 'Campaign Report: ' . $campaign->name)

@section('admin_content')
@php
    $recipientStatusCol = \Illuminate\Support\Facades\Schema::hasColumn('sms_campaign_recipients', 'sms_status') ? 'sms_status' : 'status';

    $pendingCount = $campaign->recipients()
        ->where($recipientStatusCol, 'pending')
        ->count();

    $liveDeliveredCount = max(
        (int) ($campaign->successful_count ?? $campaign->sent_count ?? 0),
        $campaign->recipients()->whereIn($recipientStatusCol, ['sent', 'delivered'])->count()
    );

    $liveFailedCount = max(
        (int) ($campaign->failed_count ?? 0),
        $campaign->recipients()->where($recipientStatusCol, 'failed')->count()
    );
@endphp

<div class="container-fluid px-3 px-md-4 py-4 sms-report-page">

    {{-- Top Header & Quick Actions --}}
    <div class="report-page-header d-flex flex-column flex-xl-row justify-content-between align-items-start align-items-xl-center gap-3 mb-4">
        <div class="report-heading">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $campaign->name }}</h4>

                @php
                    $statusClasses = [
                        'draft'     => 'bg-secondary-subtle text-secondary border',
                        'scheduled' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                        'queued'    => 'bg-info-subtle text-info border-info-subtle',
                        'running'   => 'bg-primary-subtle text-primary border-primary-subtle',
                        'completed' => 'bg-success-subtle text-success border-success-subtle',
                        'cancelled' => 'bg-light text-muted border',
                        'failed'    => 'bg-danger-subtle text-danger border-danger-subtle',
                    ];
                @endphp

                <span class="badge border report-status-badge {{ $statusClasses[$campaign->status] ?? 'bg-light text-secondary border' }}">
                    {{ ucfirst($campaign->status) }}
                </span>
            </div>

            <p class="text-muted small mb-0">
                Delivery metrics, recipient logs, and execution controls.
            </p>
        </div>

        <div class="report-actions d-flex align-items-center gap-2 flex-wrap">

            <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>

            {{-- 1-Click Gateway Live Balance Sync --}}
            <button type="button" class="btn btn-outline-dark btn-sm px-3" id="btnSyncBal" onclick="handleSyncBalance(this)">
                <i class="fas fa-sync-alt me-1" id="syncBalIcon"></i>
                <span id="syncBalText">Sync Balance</span>
            </button>

            {{-- Instant Dispatch Button --}}
            @if($pendingCount > 0)
                <form action="{{ route('admin.sms.campaigns.dispatch_now', $campaign->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Dispatch {{ $pendingCount }} pending SMS immediately?');">
                    @csrf

                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-paper-plane me-1"></i>
                        Dispatch Now ({{ $pendingCount }})
                    </button>
                </form>
            @endif

            @if(in_array($campaign->status, ['draft', 'scheduled', 'queued', 'running']) && $pendingCount > 0)
                <form action="{{ route('admin.sms.campaigns.cancel', $campaign->id) }}"
                      method="POST"
                      onsubmit="return confirm('Cancel this campaign? Pending recipients will be aborted.');">
                    @csrf

                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                        <i class="fas fa-times-circle me-1"></i> Cancel
                    </button>
                </form>
            @endif

            @if($liveFailedCount > 0)
                <form action="{{ route('admin.sms.campaigns.retry_failed', $campaign->id) }}"
                      method="POST"
                      onsubmit="return confirm('Retry failed recipients?');">
                    @csrf

                    <button type="submit" class="btn btn-warning btn-sm px-3">
                        <i class="fas fa-sync-alt me-1"></i>
                        Retry Failed ({{ $liveFailedCount }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small mb-3 report-alert" role="alert">
            <i class="fas fa-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small mb-3 report-alert" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show small mb-3 report-alert" role="alert">
            <i class="fas fa-info-circle me-1"></i>
            {{ session('info') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Performance KPI Cards --}}
    <div class="row g-3 mb-4 report-kpi-grid">

        <div class="col-6 col-md">
            <div class="card report-kpi-card h-100">
                <div class="card-body p-3">
                    <div class="report-kpi-icon icon-primary">
                        <i class="fas fa-users"></i>
                    </div>

                    <span class="text-muted small d-block mt-3">Recipients</span>
                    <h5 class="fw-bold text-dark mt-1 mb-1">
                        {{ number_format($campaign->total_recipients) }}
                    </h5>
                    <span class="text-muted report-kpi-caption">Target contacts</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md">
            <div class="card report-kpi-card h-100">
                <div class="card-body p-3">
                    <div class="report-kpi-icon icon-success">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <span class="text-muted small d-block mt-3">Delivered / Sent</span>
                    <h5 class="fw-bold text-success mt-1 mb-1">
                        {{ number_format($liveDeliveredCount) }}
                    </h5>
                    <span class="text-muted report-kpi-caption">Dispatched</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md">
            <div class="card report-kpi-card h-100">
                <div class="card-body p-3">
                    <div class="report-kpi-icon icon-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>

                    <span class="text-muted small d-block mt-3">Failed / Undelivered</span>
                    <h5 class="fw-bold text-danger mt-1 mb-1">
                        {{ number_format($liveFailedCount) }}
                    </h5>
                    <span class="text-muted report-kpi-caption">Bounced / rejected</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md">
            <div class="card report-kpi-card h-100">
                <div class="card-body p-3">
                    <div class="report-kpi-icon icon-info">
                        <i class="fas fa-wallet"></i>
                    </div>

                    <span class="text-muted small d-block mt-3">Campaign Cost</span>
                    <h5 class="fw-bold text-primary mt-1 mb-1">
                        ৳ {{ number_format((float) ($campaign->estimated_cost ?? 0), 2) }}
                    </h5>
                    <span class="text-muted report-kpi-caption">Estimated charge</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md">
            <div class="card report-kpi-card report-balance-card h-100">
                <div class="card-body p-3">
                    <div class="report-kpi-icon icon-dark">
                        <i class="fas fa-sim-card"></i>
                    </div>

                    <span class="text-muted small d-block mt-3">Live Gateway Balance</span>

                    <h5 class="fw-bold text-dark mt-1 mb-1" id="topGatewayBal">
                        ৳ {{ number_format((float) ($campaign->provider->balance ?? 0), 2) }}
                    </h5>

                    <span class="text-muted report-kpi-caption" id="topGatewayEst">
                        Est. {{ $campaign->provider && $campaign->provider->price_per_sms > 0 ? number_format((int) ($campaign->provider->balance / $campaign->provider->price_per_sms)) : 0 }} SMS left
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- Campaign Overview & Message Content --}}
    <div class="card report-card mb-4">

        <div class="card-header report-card-header">
            <div>
                <h6 class="fw-semibold mb-0">Campaign Information</h6>
                <p class="text-muted small mb-0 mt-1">Message content and campaign configuration</p>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="row g-4">

                <div class="col-12 col-md-7">

                    <label class="form-label small fw-semibold text-muted mb-2">
                        Message Content
                    </label>

                    <div class="report-message-content p-3 bg-light rounded border text-dark font-monospace small mb-4">
                        {{ $campaign->sms_message }}
                    </div>

                    @php
                        $total = max(1, (int) $campaign->total_recipients);
                        $percent = min(100, round(($liveDeliveredCount / $total) * 100));
                    @endphp

                    <div class="report-progress-section">

                        <div class="d-flex justify-content-between align-items-center mb-2 text-muted small">
                            <span class="fw-semibold">Dispatch Progress</span>

                            <strong class="text-dark">
                                {{ $percent }}%
                                ({{ number_format($liveDeliveredCount) }}/{{ number_format($total) }})
                            </strong>
                        </div>

                        <div class="progress report-progress">
                            <div class="progress-bar bg-primary"
                                 role="progressbar"
                                 style="width: {{ $percent }}%">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-12 col-md-5 report-campaign-meta">

                    <ul class="list-unstyled small mb-0">

                        <li class="d-flex justify-content-between align-items-start gap-3 py-3 border-bottom">
                            <span class="text-muted">Sender Masking:</span>
                            <span class="fw-semibold text-dark text-end">
                                {{ $campaign->sender_id ?: 'Default Mask' }}
                            </span>
                        </li>

                        <li class="d-flex justify-content-between align-items-start gap-3 py-3 border-bottom">
                            <span class="text-muted">Target Audience:</span>
                            <span class="fw-semibold text-dark text-end">
                                {{ ucfirst(str_replace('_', ' ', $campaign->target_audience)) }}
                            </span>
                        </li>

                        <li class="d-flex justify-content-between align-items-start gap-3 py-3 border-bottom">
                            <span class="text-muted">Gateway Driver:</span>
                            <span class="fw-semibold text-dark text-end">
                                {{ $campaign->provider->name ?? 'Onecodesoft' }}
                            </span>
                        </li>

                        <li class="d-flex justify-content-between align-items-start gap-3 py-3 border-bottom">
                            <span class="text-muted">Execution Timing:</span>
                            <span class="fw-semibold text-dark text-end">
                                {{ $campaign->scheduled_at ? $campaign->scheduled_at->format('M d, Y h:i A') : 'Immediate' }}
                            </span>
                        </li>

                        <li class="d-flex justify-content-between align-items-start gap-3 py-3">
                            <span class="text-muted">Created Date:</span>
                            <span class="text-dark text-end">
                                {{ $campaign->created_at->format('M d, Y h:i A') }}
                            </span>
                        </li>

                    </ul>

                </div>
            </div>
        </div>
    </div>

    {{-- Recipient Logs Filter --}}
    <div class="card report-card mb-3">

        <div class="card-body p-3">

            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="report-section-icon">
                    <i class="fas fa-filter"></i>
                </div>

                <div>
                    <h6 class="fw-semibold mb-0">Recipient Logs</h6>
                    <span class="text-muted small">Search and filter campaign recipients</span>
                </div>
            </div>

            <form action="{{ route('admin.sms.campaigns.show', $campaign->id) }}"
                  method="GET"
                  class="row g-2 align-items-center">

                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm report-search-group">

                        <span class="input-group-text bg-white text-muted">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search phone number..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status"
                            class="form-select form-select-sm"
                            onchange="this.form.submit()">

                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered / Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>

                    </select>
                </div>

                <div class="col-6 col-md-3 text-end report-filter-actions">

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.sms.campaigns.show', $campaign->id) }}"
                           class="btn btn-light btn-sm text-muted me-1">
                            Clear
                        </a>
                    @endif

                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>

                </div>

            </form>
        </div>
    </div>

    {{-- Recipients Table --}}
    <div class="card report-card">

        <div class="card-header report-card-header">
            <div>
                <h6 class="fw-semibold mb-0">Recipient Details</h6>
                <p class="text-muted small mb-0 mt-1">Individual SMS delivery records</p>
            </div>

            <span class="badge bg-light text-secondary border report-total-badge">
                {{ number_format($recipients->total()) }} Records
            </span>
        </div>

        <div class="table-responsive report-table-responsive">

            <table class="table align-middle table-hover mb-0 report-table">

                <thead>
                    <tr class="small text-muted text-uppercase">
                        <th class="ps-3 py-3">Phone Number</th>
                        <th>Customer ID</th>
                        <th>Status</th>
                        <th>Processed At</th>
                        <th class="pe-3">Failure Reason</th>
                    </tr>
                </thead>

                <tbody class="small">

                    @forelse($recipients as $recipient)

                        <tr>

                            <td class="ps-3 py-3 fw-semibold text-dark font-monospace">
                                {{ $recipient->phone_number }}
                            </td>

                            <td>
                                {{ $recipient->customer_id ? '#' . $recipient->customer_id : 'Manual / Guest' }}
                            </td>

                            <td>
                                @php
                                    $st = strtolower($recipient->sms_status ?? 'pending');

                                    $recipientBadges = [
                                        'pending'   => 'bg-secondary-subtle text-secondary border',
                                        'sent'      => 'bg-info-subtle text-info border-info-subtle',
                                        'delivered' => 'bg-success-subtle text-success border-success-subtle',
                                        'failed'    => 'bg-danger-subtle text-danger border-danger-subtle',
                                    ];
                                @endphp

                                <span class="badge border report-recipient-badge {{ $recipientBadges[$st] ?? 'bg-light text-muted border' }}">
                                    {{ ucfirst($st) }}
                                </span>
                            </td>

                            <td class="text-muted">
                                {{ $recipient->processed_at ? date('M d, Y h:i A', strtotime($recipient->processed_at)) : 'Queued' }}
                            </td>

                            <td class="pe-3 text-muted report-failure-cell">

                                @if($recipient->failure_reason)
                                    <span class="text-danger small">
                                        {{ $recipient->failure_reason }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">

                                <div class="report-empty-state">
                                    <div class="report-empty-icon">
                                        <i class="fas fa-users"></i>
                                    </div>

                                    <h6 class="fw-semibold text-dark mt-3 mb-1">
                                        No Recipients Found
                                    </h6>

                                    <p class="small text-muted mb-0">
                                        No recipient contacts found for this campaign.
                                    </p>
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        @if($recipients->hasPages())

            <div class="card-footer report-card-footer">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <span class="small text-muted">
                        Showing {{ $recipients->firstItem() }}
                        to {{ $recipients->lastItem() }}
                        of {{ $recipients->total() }} recipients
                    </span>

                    {{ $recipients->links('pagination::bootstrap-5') }}

                </div>
            </div>

        @endif

    </div>

</div>

{{-- Direct In-Content JavaScript for 1-Click Balance Sync --}}
<script>
function handleSyncBalance(button) {
    const icon = document.getElementById('syncBalIcon');
    const text = document.getElementById('syncBalText');
    const balEl = document.getElementById('topGatewayBal');
    const estEl = document.getElementById('topGatewayEst');

    button.disabled = true;
    if (icon) icon.classList.add('fa-spin');
    if (text) text.innerText = 'Syncing...';

    fetch("{{ route('admin.sms.settings.sync_balance') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (balEl) balEl.innerText = `৳ ${data.balance}`;
            if (estEl) estEl.innerText = `Est. ${data.estimated_sms} SMS left`;
        } else {
            alert(data.message || 'Balance sync failed.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while syncing balance.');
    })
    .finally(() => {
        button.disabled = false;
        if (icon) icon.classList.remove('fa-spin');
        if (text) text.innerText = 'Sync Balance';
    });
}
</script>

{{-- Kaiadmin Classic / White Classic Scoped Styles --}}
<style>
.sms-report-page {
    --report-primary: #1572e8;
    --report-primary-soft: #eaf2ff;
    --report-success: #31ce77;
    --report-danger: #f25961;
    --report-info: #48abf7;
    --report-warning: #ffad46;
    --report-text: #2a2f5b;
    --report-muted: #8d9498;
    --report-border: #ebedf2;
    --report-bg: #f8f9fa;
    color: var(--report-text);
    min-width: 0;
}

.sms-report-page .card {
    background: #fff;
    border: 1px solid var(--report-border) !important;
    border-radius: 8px !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
}

.sms-report-page .report-card {
    overflow: hidden;
}

.sms-report-page .report-card-header {
    min-height: 66px;
    padding: 15px 20px;
    background: #fff;
    border-bottom: 1px solid var(--report-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.sms-report-page .report-card-header h6 {
    color: var(--report-text);
    font-size: 14px;
    font-weight: 700;
}

.sms-report-page .report-card-header p {
    font-size: 12px;
}

.sms-report-page .report-page-header h4 {
    font-size: clamp(19px, 2vw, 23px);
    letter-spacing: -.35px;
    overflow-wrap: anywhere;
}

.sms-report-page .report-page-header p {
    font-size: 13px;
}

.sms-report-page .report-status-badge {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .35px;
    padding: 6px 10px;
    border-radius: 5px;
}

.sms-report-page .report-actions .btn {
    min-height: 34px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.sms-report-page .report-actions .btn-primary {
    background: var(--report-primary);
    border-color: var(--report-primary);
}

.sms-report-page .report-actions .btn-outline-secondary,
.sms-report-page .report-actions .btn-outline-dark,
.sms-report-page .report-actions .btn-outline-danger {
    background: #fff;
}

.sms-report-page .report-alert {
    border-radius: 6px;
    border-width: 1px;
}

.sms-report-page .report-kpi-card {
    transition: border-color .2s ease, transform .2s ease;
    border-radius: 8px !important;
}

.sms-report-page .report-kpi-card:hover {
    border-color: #d7e5fb !important;
    transform: translateY(-2px);
}

.sms-report-page .report-kpi-card .card-body {
    min-width: 0;
}

.sms-report-page .report-kpi-icon {
    width: 37px;
    height: 37px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    font-size: 15px;
}

.sms-report-page .icon-primary {
    color: var(--report-primary);
    background: var(--report-primary-soft);
}

.sms-report-page .icon-success {
    color: #24a96a;
    background: #e7f8ef;
}

.sms-report-page .icon-danger {
    color: var(--report-danger);
    background: #fff0f0;
}

.sms-report-page .icon-info {
    color: #2196e3;
    background: #eaf6ff;
}

.sms-report-page .icon-dark {
    color: #596579;
    background: #eef0f4;
}

.sms-report-page .report-kpi-card h5 {
    font-size: clamp(17px, 1.6vw, 21px);
    letter-spacing: -.3px;
    overflow-wrap: anywhere;
}

.sms-report-page .report-kpi-caption {
    display: block;
    font-size: 11px;
    line-height: 1.5;
}

.sms-report-page .report-balance-card {
    background: #fbfcfe;
}

.sms-report-page .report-message-content {
    min-height: 110px;
    white-space: pre-wrap;
    word-break: break-word;
    border-radius: 6px !important;
    border-color: var(--report-border) !important;
    line-height: 1.7;
}

.sms-report-page .report-progress {
    height: 8px;
    background: #edf0f5;
    border-radius: 10px;
    overflow: hidden;
}

.sms-report-page .report-progress .progress-bar {
    border-radius: 10px;
}

.sms-report-page .report-campaign-meta {
    border-left: 1px solid var(--report-border);
}

.sms-report-page .report-campaign-meta li {
    font-size: 12px;
    line-height: 1.5;
}

.sms-report-page .report-campaign-meta li:last-child {
    padding-bottom: 0 !important;
}

.sms-report-page .report-section-icon {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--report-primary);
    background: var(--report-primary-soft);
    border-radius: 6px;
    font-size: 13px;
}

.sms-report-page .form-control,
.sms-report-page .form-select,
.sms-report-page .input-group-text {
    min-height: 36px;
    border-color: #e6e8ef;
    border-radius: 5px;
    font-size: 12px;
    box-shadow: none;
}

.sms-report-page .form-control:focus,
.sms-report-page .form-select:focus {
    border-color: #9bc2fa;
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
}

.sms-report-page .report-search-group .input-group-text {
    border-right: 0;
    padding-left: 12px;
    padding-right: 10px;
}

.sms-report-page .report-search-group .form-control {
    border-left: 0;
}

.sms-report-page .report-filter-actions .btn {
    min-height: 34px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
}

.sms-report-page .report-table {
    min-width: 700px;
}

.sms-report-page .report-table thead th {
    background: #f8f9fc;
    color: #7b8190;
    border-bottom: 1px solid var(--report-border);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .45px;
    white-space: nowrap;
    padding-top: 14px;
    padding-bottom: 14px;
}

.sms-report-page .report-table tbody td {
    border-color: #f0f1f5;
    color: #5c6270;
    font-size: 12px;
    vertical-align: middle;
}

.sms-report-page .report-table tbody tr:last-child td {
    border-bottom: 0;
}

.sms-report-page .report-table tbody tr:hover {
    background: #fafbfd;
}

.sms-report-page .report-recipient-badge {
    padding: 5px 9px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 4px;
    white-space: nowrap;
}

.sms-report-page .report-failure-cell {
    min-width: 150px;
    max-width: 280px;
    overflow-wrap: anywhere;
}

.sms-report-page .report-total-badge {
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
}

.sms-report-page .report-empty-state {
    padding: 12px 0;
}

.sms-report-page .report-empty-icon {
    width: 54px;
    height: 54px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ba3b2;
    background: #f1f3f7;
    border-radius: 50%;
    font-size: 21px;
}

.sms-report-page .report-empty-state h6 {
    font-size: 13px;
}

.sms-report-page .report-card-footer {
    background: #fff;
    border-top: 1px solid var(--report-border);
    padding: 14px 20px;
}

.sms-report-page .pagination {
    margin-bottom: 0;
    flex-wrap: wrap;
    gap: 3px;
}

.sms-report-page .pagination .page-link {
    min-width: 30px;
    min-height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px !important;
    border-color: var(--report-border);
    color: #6c7484;
    font-size: 11px;
}

.sms-report-page .pagination .page-item.active .page-link {
    background: var(--report-primary);
    border-color: var(--report-primary);
    color: #fff;
}

.sms-report-page .pagination .page-item.disabled .page-link {
    color: #b7bdc8;
}

.sms-report-page button:disabled {
    opacity: .65;
    cursor: not-allowed;
}

/* Tablet */
@media (max-width: 991.98px) {
    .sms-report-page .report-campaign-meta {
        border-left: 0;
        border-top: 1px solid var(--report-border);
        padding-left: calc(var(--bs-gutter-x) * .5) !important;
        padding-top: 12px;
    }

    .sms-report-page .report-actions {
        width: 100%;
    }

    .sms-report-page .report-kpi-grid > div {
        flex: 0 0 auto;
        width: 33.333333%;
    }
}

/* Mobile */
@media (max-width: 575.98px) {
    .sms-report-page {
        padding-top: 18px !important;
        padding-bottom: 20px !important;
    }

    .sms-report-page .report-page-header {
        margin-bottom: 20px !important;
    }

    .sms-report-page .report-heading {
        width: 100%;
    }

    .sms-report-page .report-heading h4 {
        font-size: 19px;
    }

    .sms-report-page .report-actions {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        width: 100%;
    }

    .sms-report-page .report-actions form {
        min-width: 0;
    }

    .sms-report-page .report-actions .btn {
        width: 100%;
        padding-left: 8px !important;
        padding-right: 8px !important;
        font-size: 11px;
    }

    .sms-report-page .report-kpi-grid {
        --bs-gutter-x: 10px;
        --bs-gutter-y: 10px;
    }

    .sms-report-page .report-kpi-grid > div {
        width: 50%;
    }

    .sms-report-page .report-kpi-card .card-body {
        padding: 12px !important;
    }

    .sms-report-page .report-kpi-card h5 {
        font-size: 17px;
    }

    .sms-report-page .report-card-header {
        padding: 13px 14px;
    }

    .sms-report-page .report-card-body,
    .sms-report-page .card-body {
        min-width: 0;
    }

    .sms-report-page .report-filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 5px;
    }

    .sms-report-page .report-card-footer {
        padding: 12px 14px;
    }

    .sms-report-page .report-card-footer > div {
        align-items: flex-start !important;
    }

    .sms-report-page .report-card-footer .pagination {
        width: 100%;
    }

    .sms-report-page .report-card-footer .pagination .page-link {
        min-width: 28px;
        min-height: 28px;
        padding: 5px 8px;
    }

    .sms-report-page .report-table {
        min-width: 700px;
    }
}

/* Small Mobile */
@media (max-width: 360px) {
    .sms-report-page .report-kpi-grid > div {
        width: 100%;
    }

    .sms-report-page .report-actions {
        grid-template-columns: 1fr;
    }

    .sms-report-page .report-actions .btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')
    @yield('admin_content')
@endsection