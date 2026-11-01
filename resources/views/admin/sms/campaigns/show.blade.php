@extends('layouts.admin')

@section('title', 'Campaign Report: ' . $campaign->name)

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Top Header & Quick Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">{{ $campaign->name }}</h4>
                @php
                    $statusClasses = [
                        'draft' => 'bg-secondary-subtle text-secondary border',
                        'scheduled' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                        'queued' => 'bg-info-subtle text-info border-info-subtle',
                        'running' => 'bg-primary-subtle text-primary border-primary-subtle',
                        'completed' => 'bg-success-subtle text-success border-success-subtle',
                        'cancelled' => 'bg-light text-muted border',
                        'failed' => 'bg-danger-subtle text-danger border-danger-subtle',
                    ];
                @endphp
                <span class="badge border {{ $statusClasses[$campaign->status] ?? 'bg-light text-secondary border' }}">
                    {{ ucfirst($campaign->status) }}
                </span>
            </div>
            <p class="text-muted small mb-0">Delivery metrics, recipient logs, and execution controls.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>

            @if(in_array($campaign->status, ['draft', 'scheduled', 'queued', 'running']))
                <form action="{{ route('admin.sms.campaigns.cancel', $campaign->id) }}" method="POST" onsubmit="return confirm('Cancel this campaign? Pending recipients will be aborted.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                        <i class="fas fa-times-circle me-1"></i> Cancel
                    </button>
                </form>
            @endif

            @if(in_array($campaign->status, ['completed', 'failed']) && ($campaign->failed_count ?? 0) > 0)
                <form action="{{ route('admin.sms.campaigns.retry_failed', $campaign->id) }}" method="POST" onsubmit="return confirm('Retry failed recipients in background?');">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm px-3">
                        <i class="fas fa-sync-alt me-1"></i> Retry Failed
                    </button>
                </form>
            @endif
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

    {{-- Performance KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Recipients</span>
                    <h5 class="fw-bold text-dark mt-1 mb-0">{{ number_format($campaign->total_recipients) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Target pool</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Delivered / Sent</span>
                    <h5 class="fw-bold text-success mt-1 mb-0">{{ number_format($campaign->successful_count ?? $campaign->sent_count ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Dispatched</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Failed</span>
                    <h5 class="fw-bold text-danger mt-1 mb-0">{{ number_format($campaign->failed_count ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Undelivered</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Estimated Cost</span>
                    <h5 class="fw-bold text-primary mt-1 mb-0">৳ {{ number_format((float) ($campaign->estimated_cost ?? 0), 2) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Provider cost</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Campaign Overview & Message Content --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0 text-dark">Campaign Information</h6>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <label class="form-label small text-muted mb-1">Message Content</label>
                    <div class="p-3 bg-light rounded border text-dark font-monospace small mb-3" style="white-space: pre-wrap; word-break: break-word;">
                        {{ $campaign->sms_message }}
                    </div>

                    @php
                        $total = max(1, (int) $campaign->total_recipients);
                        $sent = (int) ($campaign->successful_count ?? $campaign->sent_count ?? 0);
                        $percent = min(100, round(($sent / $total) * 100));
                    @endphp
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1 text-muted small">
                            <span>Dispatch Progress</span>
                            <strong>{{ $percent }}% ({{ number_format($sent) }}/{{ number_format($total) }})</strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-5 border-start-md ps-md-4">
                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Sender Masking:</span>
                            <span class="fw-semibold text-dark">{{ $campaign->sender_id ?: 'Default Mask' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Target Audience:</span>
                            <span class="fw-semibold text-dark">{{ ucfirst(str_replace('_', ' ', $campaign->target_audience)) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Gateway Driver:</span>
                            <span class="fw-semibold text-dark">{{ $campaign->provider->name ?? 'Onecodesoft' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Execution Timing:</span>
                            <span class="fw-semibold text-dark">
                                {{ $campaign->scheduled_at ? $campaign->scheduled_at->format('M d, Y h:i A') : 'Immediate' }}
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Created Date:</span>
                            <span class="text-dark">{{ $campaign->created_at->format('M d, Y h:i A') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Recipient Logs with Filter Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.campaigns.show', $campaign->id) }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search phone number..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 text-end">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.sms.campaigns.show', $campaign->id) }}" class="btn btn-light btn-sm px-2 text-muted me-1">Clear</a>
                    @endif
                    <button type="submit" class="btn btn-secondary btn-sm px-3">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Recipients Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase" style="font-size: 11px;">
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
                            <td class="ps-3 py-2 fw-semibold text-dark">
                                {{ $recipient->phone_number }}
                            </td>
                            <td>
                                {{ $recipient->customer_id ? '#' . $recipient->customer_id : 'Guest' }}
                            </td>
                            <td>
                                @php
                                    $recipientBadges = [
                                        'pending' => 'bg-secondary-subtle text-secondary border',
                                        'sent' => 'bg-info-subtle text-info border-info-subtle',
                                        'delivered' => 'bg-success-subtle text-success border-success-subtle',
                                        'failed' => 'bg-danger-subtle text-danger border-danger-subtle',
                                    ];
                                @endphp
                                <span class="badge border {{ $recipientBadges[$recipient->sms_status] ?? 'bg-light text-muted border' }}">
                                    {{ ucfirst($recipient->sms_status) }}
                                </span>
                            </td>
                            <td class="text-muted">
                                {{ $recipient->processed_at ? $recipient->processed_at->format('M d, Y h:i A') : 'Queued' }}
                            </td>
                            <td class="pe-3 text-muted">
                                @if($recipient->failure_reason)
                                    <span class="text-danger small">{{ $recipient->failure_reason }}</span>
                                @else
                                    <span class="text-muted" style="font-size: 11px;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-users fs-3 d-block mb-2 text-secondary"></i>
                                No recipients found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recipients->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Showing {{ $recipients->firstItem() }} to {{ $recipients->lastItem() }} of {{ $recipients->total() }} recipients
                    </span>
                    {{ $recipients->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection

@section('content')
    @yield('admin_content')
@endsection