@extends('layouts.admin')

@section('title', 'Support Requests - ' . config('app.name'))

@section('admin_content')
<style>
    /* Customer Support — Kaiadmin Lite Classic / White */
    #supportIndexRoot {
        --support-border: #e5e7eb;
        --support-muted: #6b7280;
        --support-heading: #374151;
        --support-surface: #ffffff;
        --support-soft: #f8fafc;
        --support-primary: var(--bs-primary, #6861ce);
        color: #374151;
        min-width: 0;
    }

    #supportIndexRoot .btn,
    #supportIndexRoot .badge,
    #supportIndexRoot .alert,
    #supportIndexRoot .page-link,
    #supportIndexRoot .page-item span {
        border-radius: 5px;
    }

    #supportIndexRoot .support-table-card {
        background: var(--support-surface);
        border: 1px solid var(--support-border);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(31, 41, 55, .045);
        margin-bottom: 20px;
        min-width: 0;
    }

    #supportIndexRoot h5 {
        color: #343a40;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.2px;
        line-height: 1.4;
    }

    #supportIndexRoot .support-filter-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        min-width: 0;
    }

    #supportIndexRoot .support-filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 6px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
    }

    #supportIndexRoot .support-filter-btn:focus-visible,
    #supportIndexRoot .btn:focus-visible,
    #supportIndexRoot a:focus-visible {
        outline: 2px solid rgba(104, 97, 206, .35);
        outline-offset: 2px;
        box-shadow: none;
    }

    #supportIndexRoot .support-filter-btn.btn-outline-warning {
        color: #a16207;
        border-color: #f3d28a;
        background: #fffbeb;
    }

    #supportIndexRoot .support-filter-btn.btn-outline-warning:hover {
        color: #854d0e;
        border-color: #eabf55;
        background: #fef3c7;
    }

    #supportIndexRoot .custom-support-table {
        width: 100%;
        margin-bottom: 0;
        color: #374151;
        border-collapse: separate;
        border-spacing: 0;
    }

    #supportIndexRoot .custom-support-table thead th {
        padding: 12px 16px;
        background: #f8f9fa;
        color: #6b7280;
        border-bottom: 1px solid var(--support-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .045em;
        line-height: 1.4;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #supportIndexRoot .custom-support-table tbody td {
        padding: 13px 16px;
        color: #374151;
        border-bottom: 1px solid #eef0f2;
        font-size: 12.5px;
        line-height: 1.5;
        vertical-align: middle;
    }

    #supportIndexRoot .custom-support-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #supportIndexRoot .custom-support-table tbody tr {
        transition: background-color .15s ease;
    }

    #supportIndexRoot .custom-support-table tbody tr:hover {
        background: #fafbfc;
    }

    #supportIndexRoot .custom-support-table .text-muted {
        color: var(--support-muted) !important;
    }

    #supportIndexRoot .custom-support-table .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 23px;
        padding: 4px 9px;
        border: 1px solid transparent;
        border-radius: 5px;
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }

    #supportIndexRoot .custom-support-table .badge-pending {
        color: #b42318;
        background: #fef3f2;
        border-color: #fecdca;
    }

    #supportIndexRoot .custom-support-table .badge-checked {
        color: #946200;
        background: #fffaeb;
        border-color: #fedf89;
    }

    #supportIndexRoot .custom-support-table .badge-resolved {
        color: #027a48;
        background: #ecfdf3;
        border-color: #abefc6;
    }

    #supportIndexRoot .custom-support-table .badge-closed {
        color: #475467;
        background: #f2f4f7;
        border-color: #e4e7ec;
    }

    #supportIndexRoot .bn-digit,
    #supportIndexRoot .font-monospace {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    #supportIndexRoot .custom-support-table .btn {
        min-height: 29px;
        border-radius: 5px;
        font-size: 11.5px;
        font-weight: 600;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }

    #supportIndexRoot .card-footer {
        border-color: var(--support-border) !important;
    }

    #supportIndexRoot .pagination {
        flex-wrap: wrap;
        gap: 3px;
        margin-bottom: 0;
    }

    #supportIndexRoot .page-link {
        min-width: 30px;
        text-align: center;
        border-color: var(--support-border);
        color: #5b6472;
        font-size: 12px;
    }

    #supportIndexRoot .page-item.active .page-link {
        color: #fff;
        background: var(--support-primary);
        border-color: var(--support-primary);
    }

    #supportIndexRoot .alert {
        border: 1px solid #abefc6 !important;
        border-radius: 6px;
    }

    @media (max-width: 991.98px) {
        #supportIndexRoot > .d-flex {
            align-items: flex-start !important;
        }

        #supportIndexRoot .support-filter-group {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        #supportIndexRoot {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #supportIndexRoot > .d-flex {
            gap: 10px !important;
            margin-bottom: 14px !important;
        }

        #supportIndexRoot h5 {
            margin-bottom: 2px !important;
            font-size: 17px !important;
        }

        #supportIndexRoot .support-filter-group {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            width: 100%;
            gap: 6px;
        }

        #supportIndexRoot .support-filter-btn {
            width: 100%;
            min-width: 0;
            min-height: 34px;
            padding: 6px 5px;
            font-size: 11px;
            white-space: normal;
            text-align: center;
        }

        #supportIndexRoot .support-table-card {
            border-radius: 7px;
        }

        #supportIndexRoot .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #supportIndexRoot .custom-support-table {
            min-width: 820px;
        }

        #supportIndexRoot .custom-support-table thead th {
            padding: 11px 12px;
            font-size: 10px;
        }

        #supportIndexRoot .custom-support-table tbody td {
            padding: 11px 12px;
            font-size: 12px;
        }

        #supportIndexRoot .card-footer {
            padding: 10px 12px !important;
        }
    }

    @media (max-width: 380px) {
        #supportIndexRoot .support-filter-group {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #supportIndexRoot *,
        #supportIndexRoot *::before,
        #supportIndexRoot *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
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