@extends('layouts.admin')

@section('title', 'SMS Campaigns')

@section('admin_content')
<style>
    .sms-campaigns-page {
        --sms-primary: #1572e8;
        --sms-text: #1f2937;
        --sms-muted: #7b8493;
        --sms-border: #e8edf3;
        --sms-surface: #fff;
        color: var(--sms-text);
        min-width: 0;
    }
    .sms-campaigns-page .sms-page-title { color: #202939; font-weight: 700; letter-spacing: -.025em; }
    .sms-campaigns-page .sms-page-subtitle { color: var(--sms-muted); }
    .sms-campaigns-page .sms-card {
        background: var(--sms-surface);
        border: 1px solid var(--sms-border) !important;
        border-radius: 12px;
        box-shadow: 0 3px 14px rgba(31, 41, 55, .045) !important;
        min-width: 0;
    }
    .sms-campaigns-page .sms-metric-card { position: relative; overflow: hidden; transition: box-shadow .2s ease, transform .2s ease; }
    .sms-campaigns-page .sms-metric-card:hover { transform: translateY(-2px); box-shadow: 0 7px 20px rgba(31,41,55,.08) !important; }
    .sms-campaigns-page .sms-metric-label { color: #788394; font-size: 12px; font-weight: 600; letter-spacing: .015em; }
    .sms-campaigns-page .sms-metric-value { color: #202939; font-size: 1.45rem; font-weight: 700; line-height: 1.25; }
    .sms-campaigns-page .sms-metric-note { color: #929baa; font-size: 11px; line-height: 1.45; }
    .sms-campaigns-page .sms-filter-card .form-control,
    .sms-campaigns-page .sms-filter-card .form-select,
    .sms-campaigns-page .sms-filter-card .input-group-text { min-height: 36px; border-color: #e2e8f0; }
    .sms-campaigns-page .sms-filter-card .form-control:focus,
    .sms-campaigns-page .sms-filter-card .form-select:focus { border-color: #9bc4fa; box-shadow: 0 0 0 .18rem rgba(21,114,232,.1); }
    .sms-campaigns-page .sms-table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .sms-campaigns-page .sms-campaign-table { min-width: 930px; margin-bottom: 0; }
    .sms-campaigns-page .sms-campaign-table thead th { background: #f7f9fc; color: #7a8494; border-bottom: 1px solid var(--sms-border); font-size: 10px; font-weight: 700; letter-spacing: .055em; white-space: nowrap; }
    .sms-campaigns-page .sms-campaign-table tbody td { border-color: #edf1f5; color: #4b5563; vertical-align: middle; }
    .sms-campaigns-page .sms-campaign-table tbody tr:last-child td { border-bottom: 0; }
    .sms-campaigns-page .sms-campaign-name { color: #263244 !important; overflow-wrap: anywhere; }
    .sms-campaigns-page .sms-campaign-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; color: #8993a2; }
    .sms-campaigns-page .sms-campaign-table .badge { display: inline-block; max-width: 100%; padding: .42em .65em; font-size: 10px; font-weight: 600; white-space: normal; line-height: 1.35; }
    .sms-campaigns-page .sms-progress { min-width: 135px; }
    .sms-campaigns-page .sms-campaign-table .btn-group .btn { min-width: 31px; }
    .sms-campaigns-page .sms-campaign-table .btn:focus-visible,
    .sms-campaigns-page .btn:focus-visible { outline: 2px solid rgba(21,114,232,.35); outline-offset: 2px; }
    .sms-campaigns-page .sms-pagination { overflow-x: auto; max-width: 100%; }
    .sms-campaigns-page .card-footer { border-top: 1px solid var(--sms-border) !important; }
    @media (max-width: 767.98px) {
        .sms-campaigns-page { padding-top: 1rem !important; padding-bottom: 1rem !important; }
        .sms-campaigns-page .sms-page-header { align-items: stretch !important; }
        .sms-campaigns-page .sms-page-header .btn { align-self: flex-start; }
        .sms-campaigns-page .sms-metric-card .card-body { padding: .85rem !important; }
        .sms-campaigns-page .sms-metric-value { font-size: 1.25rem; }
        .sms-campaigns-page .sms-metric-note { min-height: 2.8em; }
        .sms-campaigns-page .sms-filter-card .card-body { padding: .85rem !important; }
        .sms-campaigns-page .sms-filter-actions { display: flex; justify-content: flex-end; gap: .4rem; }
        .sms-campaigns-page .sms-campaign-table { min-width: 900px; }
        .sms-campaigns-page .sms-footer-content { align-items: flex-start !important; }
    }
    @media (max-width: 380px) {
        .sms-campaigns-page .sms-metric-label { font-size: 11px; }
        .sms-campaigns-page .sms-metric-value { font-size: 1.15rem; }
        .sms-campaigns-page .sms-metric-note { font-size: 10px; }
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4 sms-campaigns-page">
    {{-- Top Header & New Campaign Button --}}
    <div class="sms-page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="sms-page-title mb-1">SMS Campaigns</h4>
            <p class="sms-page-subtitle small mb-0">Track and manage marketing and promotional broadcasts.</p>
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
            <div class="card sms-card sms-metric-card h-100"><div class="card-body p-3">
                <span class="sms-metric-label d-block">Total Campaigns</span>
                <h5 class="sms-metric-value mt-1 mb-1">{{ number_format($stats['total'] ?? 0) }}</h5>
                <span class="sms-metric-note">All lifetime records</span>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card sms-card sms-metric-card h-100"><div class="card-body p-3">
                <span class="sms-metric-label d-block">Running</span>
                <h5 class="sms-metric-value text-primary mt-1 mb-1">{{ number_format($stats['running'] ?? 0) }}</h5>
                <span class="sms-metric-note">Active in background queue</span>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card sms-card sms-metric-card h-100"><div class="card-body p-3">
                <span class="sms-metric-label d-block">Scheduled</span>
                <h5 class="sms-metric-value text-warning mt-1 mb-1">{{ number_format($stats['scheduled'] ?? 0) }}</h5>
                <span class="sms-metric-note">Awaiting trigger time</span>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card sms-card sms-metric-card h-100"><div class="card-body p-3">
                <span class="sms-metric-label d-block">Completed</span>
                <h5 class="sms-metric-value text-success mt-1 mb-1">{{ number_format($stats['completed'] ?? 0) }}</h5>
                <span class="sms-metric-note">Finished delivery</span>
            </div></div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card sms-card sms-filter-card mb-3"><div class="card-body p-3">
        <form action="{{ route('admin.sms.campaigns.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
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
            <div class="col-6 col-md-3 text-end sms-filter-actions">
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.sms.campaigns.index') }}" class="btn btn-light btn-sm px-2 text-muted me-1">Clear</a>
                @endif
                <button type="submit" class="btn btn-secondary btn-sm px-3">Filter</button>
            </div>
        </form>
    </div></div>

    {{-- Campaign Table Card --}}
    <div class="card sms-card">
        <div class="sms-table-wrap">
            <table class="table align-middle table-hover sms-campaign-table">
                <thead><tr class="small text-muted text-uppercase">
                    <th class="ps-3 py-3">Campaign</th>
                    <th>Target Audience</th>
                    <th style="min-width: 170px;">Delivery Progress</th>
                    <th>Status</th>
                    <th>Execution Time</th>
                    <th class="pe-3 text-end">Actions</th>
                </tr></thead>
                <tbody class="small">
                    @forelse($campaigns as $campaign)
                        <tr>
                            {{-- Campaign Name & Sender ID --}}
                            <td class="ps-3 py-3">
                                <a href="{{ route('admin.sms.campaigns.show', $campaign->id) }}" class="sms-campaign-name fw-semibold text-decoration-none d-block">{{ $campaign->name }}</a>
                                <div class="sms-campaign-meta mt-1" style="font-size: 11px;">
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
                                <span class="badge bg-light text-dark border">{{ $audienceLabels[$campaign->target_audience] ?? ucfirst($campaign->target_audience) }}</span>
                                <div class="text-muted mt-1" style="font-size: 11px;">{{ number_format($campaign->total_recipients) }} recipients</div>
                            </td>
                            {{-- Progress Bar & Metrics --}}
                            <td><div class="sms-progress">
                                @php
                                    $total = max(1, (int) $campaign->total_recipients);
                                    $sent = (int) ($campaign->successful_count ?? $campaign->sent_count ?? 0);
                                    $failed = (int) ($campaign->failed_count ?? 0);
                                    $percent = min(100, round(($sent / $total) * 100));
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-1 text-muted" style="font-size: 11px;">
                                    <span>Sent: {{ number_format($sent) }}</span><span>{{ $percent }}%</span>
                                </div>
                                <div class="progress" style="height: 5px;"><div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div></div>
                                @if($failed > 0)<div class="text-danger mt-1" style="font-size: 10px;">{{ number_format($failed) }} failed</div>@endif
                            </div></td>
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
                                <span class="badge border {{ $statusClasses[$campaign->status] ?? 'bg-light text-secondary border' }}">{{ ucfirst($campaign->status) }}</span>
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
                                    <a href="{{ route('admin.sms.campaigns.show', $campaign->id) }}" class="btn btn-light" title="View Report"><i class="fas fa-eye text-secondary"></i></a>
                                    @if(in_array($campaign->status, ['draft', 'scheduled', 'queued']))
                                        <form action="{{ route('admin.sms.campaigns.cancel', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this campaign?');">
                                            @csrf
                                            <button type="submit" class="btn btn-light text-danger" title="Cancel Campaign"><i class="fas fa-times-circle"></i></button>
                                        </form>
                                    @endif
                                    @if(in_array($campaign->status, ['completed', 'failed']) && ($campaign->failed_count ?? 0) > 0)
                                        <form action="{{ route('admin.sms.campaigns.retry_failed', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Retry failed recipients?');">
                                            @csrf
                                            <button type="submit" class="btn btn-light text-warning" title="Retry Failed"><i class="fas fa-sync-alt"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-bullhorn fs-3 d-block mb-2 text-secondary"></i>
                            No SMS campaigns found.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="sms-footer-content d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">Showing {{ $campaigns->firstItem() }} to {{ $campaigns->lastItem() }} of {{ $campaigns->total() }} campaigns</span>
                    <div class="sms-pagination">{{ $campaigns->links() }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('content')
    @yield('admin_content')
@endsection
