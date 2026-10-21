@extends('layouts.admin')

@section('title', 'Support Requests - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #supportIndexRoot .btn,
    #supportIndexRoot .btn-sm,
    #supportIndexRoot .badge,
    #supportIndexRoot .alert,
    #supportIndexRoot .page-link,
    #supportIndexRoot .page-item span {
        border-radius: 5px !important;
    }

    #supportIndexRoot .support-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }

    /* Filter Navigation Pills (Individual 5px Buttons with Readable Contrast) */
    .support-filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .support-filter-btn {
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 6px 12px !important;
        white-space: nowrap !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }

    /* Fix unreadable yellow outline text on white background */
    .support-filter-btn.btn-outline-warning {
        color: #b45309 !important;
        border-color: #fcd34d !important;
        background-color: #fffbeb;
    }
    .support-filter-btn.btn-outline-warning:hover {
        background-color: #fef3c7 !important;
        color: #92400e !important;
    }

    /* Table Typography & Layout */
    .custom-support-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 11px 16px;
        white-space: nowrap;
    }

    .custom-support-table td {
        padding: 12px 16px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-support-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Soft Status Badges */
    .status-badge {
        font-size: 10.5px;
        font-weight: 600;
        padding: 4px 10px;
        display: inline-block;
        white-space: nowrap;
    }
    .badge-pending  { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-checked  { background-color: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .badge-resolved { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-closed   { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .support-filter-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }
        .support-filter-btn {
            width: 100%;
            padding: 6px 6px !important;
            font-size: 11.5px !important;
        }
        .custom-support-table th,
        .custom-support-table td {
            padding: 10px 12px;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="supportIndexRoot">
    <!-- Top Header & Status Filter Bar -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2.5 mb-3">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center" style="font-size: 18px;">
            <i class="fas fa-headset text-warning me-2"></i>
            <span>Customer Support Requests</span>
        </h5>
        
        <!-- Status Filter Badges -->
        <div class="support-filter-group" role="group">
            <a href="{{ route('admin.support.index') }}" class="btn btn-sm support-filter-btn {{ request('status') == '' ? 'btn-dark' : 'btn-outline-dark' }}">
                All (<span class="bn-digit">{{ $counts['all'] }}</span>)
            </a>
            <a href="{{ route('admin.support.index', ['status' => 'pending']) }}" class="btn btn-sm support-filter-btn {{ request('status') == 'pending' ? 'btn-danger' : 'btn-outline-danger' }}">
                Pending (<span class="bn-digit">{{ $counts['pending'] }}</span>)
            </a>
            <a href="{{ route('admin.support.index', ['status' => 'checked']) }}" class="btn btn-sm support-filter-btn {{ request('status') == 'checked' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                Checked (<span class="bn-digit">{{ $counts['checked'] }}</span>)
            </a>
            <a href="{{ route('admin.support.index', ['status' => 'resolved']) }}" class="btn btn-sm support-filter-btn {{ request('status') == 'resolved' ? 'btn-success' : 'btn-outline-success' }}">
                Resolved (<span class="bn-digit">{{ $counts['resolved'] }}</span>)
            </a>
            <a href="{{ route('admin.support.index', ['status' => 'closed']) }}" class="btn btn-sm support-filter-btn {{ request('status') == 'closed' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                Closed (<span class="bn-digit">{{ $counts['closed'] }}</span>)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
            <span class="fw-semibold">{{ session('success') }}</span>
            <button type="button" class="btn-close btn-sm shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tickets Table Card -->
    <div class="card border-0 support-table-card overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table custom-support-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 ps-md-4">Ticket No</th>
                            <th style="min-width: 160px;">Customer Info</th>
                            <th style="min-width: 220px;">Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3 pe-md-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $t)
                            <tr>
                                <td class="ps-3 ps-md-4 fw-bold font-monospace text-primary text-nowrap bn-digit" style="font-size: 12.5px;">
                                    {{ $t->ticket_no }}
                                </td>
                                <td>
                                    <strong class="text-dark d-block text-truncate" style="max-width: 170px; font-size: 13px;">{{ $t->user_name ?? 'Guest' }}</strong>
                                    <small class="text-muted font-monospace d-block text-truncate bn-digit" style="max-width: 170px; font-size: 11px;">{{ $t->user_email ?? ($t->user_phone ?? 'N/A') }}</small>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold d-block" style="font-size: 12.5px;">{{ Str::limit($t->subject, 35) }}</span>
                                </td>
                                <td class="text-nowrap">
                                    @if($t->status == 'pending')
                                        <span class="badge status-badge badge-pending">Pending</span>
                                    @elseif($t->status == 'checked')
                                        <span class="badge status-badge badge-checked">Checked</span>
                                    @elseif($t->status == 'resolved')
                                        <span class="badge status-badge badge-resolved">Resolved</span>
                                    @else
                                        <span class="badge status-badge badge-closed">Closed</span>
                                    @endif
                                </td>
                                <td class="small text-muted text-nowrap font-monospace bn-digit" style="font-size: 11.5px;">
                                    {{ date('d M Y, h:i A', strtotime($t->created_at)) }}
                                </td>
                                <td class="text-end pe-3 pe-md-4 text-nowrap">
                                    <a href="{{ route('admin.support.show', $t->id) }}" class="btn btn-sm btn-outline-primary px-2.5 py-1 fw-semibold d-inline-flex align-items-center text-nowrap shadow-xs" style="font-size: 11.5px;">
                                        <i class="fas fa-reply me-1.5"></i>
                                        <span>View & Reply</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted small">
                                    <span class="fw-semibold">No support tickets found.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer bg-white border-top py-2.5 px-3">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection