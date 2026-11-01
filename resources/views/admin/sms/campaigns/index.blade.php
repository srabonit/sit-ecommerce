@extends('layouts.admin')

@section('title', 'SMS Campaigns')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Top Header & New Campaign Button --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">SMS Campaigns</h4>
            <p class="text-muted small mb-0">Track and manage marketing and promotional broadcasts.</p>
        </div>
        <a href="{{ route('admin.sms.campaigns.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-bullhorn me-1"></i> New Campaign
        </a>
    </div>

    {{-- Session Alerts --}}
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

    {{-- Quick Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Total Campaigns</span>
                    <h5 class="fw-bold text-dark mt-1 mb-0">{{ number_format($stats['total'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">All lifetime records</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Running</span>
                    <h5 class="fw-bold text-primary mt-1 mb-0">{{ number_format($stats['running'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Active in background queue</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Scheduled</span>
                    <h5 class="fw-bold text-warning mt-1 mb-0">{{ number_format($stats['scheduled'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Awaiting trigger time</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Completed</span>
                    <h5 class="fw-bold text-success mt-1 mb-0">{{ number_format($stats['completed'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Finished delivery</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.campaigns.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by campaign name..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>Queued</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 text-end">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-light btn-sm px-2 text-muted me-1">Clear</a>
                    @endif
                    <button type="submit" class="btn btn-secondary btn-sm px-3">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Campaign Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                        <th class="ps-3 py-3">Campaign</th>
                        <th>Target Audience</th>
                        <th style="min-width: 170px;">Delivery Progress</th>
                        <th>Status</th>
                        <th>Execution Time</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($campaigns as $campaign)
                        <tr>
                            {{-- Campaign Name & Sender ID --}}
                            <td class="ps-3 py-3">
                                <a href="{{ route('admin.sms.campaigns.show', $campaign->id) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                    {{ $campaign->name }}
                                </a>
                                <div class="text-muted d-flex align-items-center gap-2 mt-1" style="font-size: 11px;">
                                    <span>Sender: <strong>{{ $campaign->sender_id ?: 'Default' }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Cost: <strong>৳{{ number_format((float) ($campaign->estimated_cost ?? 0), 2) }}</strong></span>
                                </div>
                            </td>

                            {{-- Target Audience --}}
                            <td>
                                @php
                                    $audienceLabels = [
                                        'all_users' => 'All Users',
                                        'subscribed_only' => 'Subscribers Only',
                                        'recent_buyers' => 'Recent Buyers',
                                        'custom_numbers' => 'Custom List',
                                    ];
                                @endphp
                                <span class="badge bg-light text-dark border">
                                    {{ $audienceLabels[$campaign->target_audience] ?? ucfirst($campaign->target_audience) }}
                                </span>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    {{ number_format($campaign->total_recipients) }} recipients
                                </div>
                            </td>

                            {{-- Progress Bar & Metrics --}}
                            <td>
                                @php
                                    $total = max(1, (int) $campaign->total_recipients);
                                    $sent = (int) ($campaign->successful_count ?? $campaign->sent_count ?? 0);
                                    $failed = (int) ($campaign->failed_count ?? 0);
                                    $percent = min(100, round(($sent / $total) * 100));
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-1 text-muted" style="font-size: 11px;">
                                    <span>Sent: {{ number_format($sent) }}</span>
                                    <span>{{ $percent }}%</span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                                </div>
                                @if($failed > 0)
                                    <div class="text-danger mt-1" style="font-size: 10px;">
                                        {{ number_format($failed) }} failed
                                    </div>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td>
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
                            </td>

                            {{-- Schedule & Date --}}
                            <td>
                                @if($campaign->scheduled_at)
                                    <div class="fw-medium text-dark">{{ $campaign->scheduled_at->format('M d, Y') }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $campaign->scheduled_at->format('h:i A') }}</div>
                                @else
                                    <div class="fw-medium text-dark">{{ $campaign->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted" style="font-size: 11px;">Immediate</div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.sms.campaigns.show', $campaign->id) }}" class="btn btn-light" title="View Report">
                                        <i class="fas fa-eye text-secondary"></i>
                                    </a>

                                    @if(in_array($campaign->status, ['draft', 'scheduled', 'queued']))
                                        <form action="{{ route('admin.sms.campaigns.cancel', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this campaign?');">
                                            @csrf
                                            <button type="submit" class="btn btn-light text-danger" title="Cancel Campaign">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($campaign->status, ['completed', 'failed']) && ($campaign->failed_count ?? 0) > 0)
                                        <form action="{{ route('admin.sms.campaigns.retry_failed', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Retry failed recipients?');">
                                            @csrf
                                            <button type="submit" class="btn btn-light text-warning" title="Retry Failed">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-bullhorn fs-3 d-block mb-2 text-secondary"></i>
                                No SMS campaigns found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Showing {{ $campaigns->firstItem() }} to {{ $campaigns->lastItem() }} of {{ $campaigns->total() }} campaigns
                    </span>
                    {{ $campaigns->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection

@section('content')
    @yield('admin_content')
@endsection