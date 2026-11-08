@extends('layouts.admin')

@section('title', 'Ticket Details - ' . config('app.name'))

@section('admin_content')
<style>
    /* Ticket details — Kaiadmin Lite Classic / White */
    #ticketDetailsRoot {
        --ticket-border: #e6eaf0;
        --ticket-muted: #6b7280;
        --ticket-text: #344054;
        --ticket-heading: #1f2937;
        --ticket-surface: #ffffff;
        --ticket-soft: #f8fafc;
        --ticket-primary: #1572e8;
        --ticket-focus: rgba(21, 114, 232, 0.14);
        color: var(--ticket-text);
    }

    #ticketDetailsRoot .btn,
    #ticketDetailsRoot .form-control,
    #ticketDetailsRoot .form-select,
    #ticketDetailsRoot .user-msg-box {
        border-radius: 5px;
    }

    #ticketDetailsRoot .ticket-page-header {
        gap: 12px;
        margin-bottom: 18px;
    }

    #ticketDetailsRoot .ticket-page-title {
        color: var(--ticket-heading);
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
        margin: 0;
        overflow-wrap: anywhere;
    }

    #ticketDetailsRoot .ticket-page-title .ticket-number {
        color: var(--ticket-primary);
        overflow-wrap: anywhere;
    }

    #ticketDetailsRoot .header-back-btn {
        min-height: 34px;
        border-color: #d0d5dd;
        color: #475467;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 12px;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }

    #ticketDetailsRoot .header-back-btn:hover {
        background: #f8fafc;
        border-color: #98a2b3;
        color: #1d2939;
    }

    #ticketDetailsRoot .ticket-card {
        height: 100%;
        background: var(--ticket-surface);
        border: 1px solid var(--ticket-border);
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .045);
        padding: 20px;
    }

    #ticketDetailsRoot .ticket-section-heading {
        align-items: center;
        border-bottom: 1px solid var(--ticket-border);
        color: var(--ticket-heading);
        display: flex;
        font-size: 13px;
        font-weight: 700;
        justify-content: space-between;
        line-height: 1.4;
        margin: 0 0 16px;
        padding-bottom: 11px;
    }

    #ticketDetailsRoot .ticket-meta-item {
        background: var(--ticket-soft);
        border: 1px solid #edf0f4;
        border-radius: 5px;
        color: var(--ticket-text);
        display: flex;
        flex-direction: column;
        font-size: 12.5px;
        gap: 4px;
        margin-bottom: 9px;
        min-width: 0;
        overflow-wrap: anywhere;
        padding: 9px 11px;
    }

    #ticketDetailsRoot .ticket-meta-item strong {
        color: var(--ticket-muted);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    #ticketDetailsRoot .ticket-meta-item > span {
        line-height: 1.5;
    }

    #ticketDetailsRoot .ticket-message-heading {
        color: var(--ticket-heading);
        display: block;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    #ticketDetailsRoot .user-msg-box {
        background: #f8fafc;
        border: 1px solid var(--ticket-border);
        color: #475467;
        font-size: 12.5px;
        line-height: 1.65;
        overflow-wrap: anywhere;
        padding: 13px 14px;
        white-space: pre-line;
    }

    #ticketDetailsRoot .form-label {
        color: #475467;
        font-size: 11.5px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    #ticketDetailsRoot .form-control,
    #ticketDetailsRoot .form-select {
        background-color: #fff;
        border: 1px solid #d0d5dd;
        color: #344054;
        font-size: 12.5px;
        min-height: 38px;
        padding: 8px 11px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #ticketDetailsRoot .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    #ticketDetailsRoot .form-control:focus,
    #ticketDetailsRoot .form-select:focus {
        border-color: var(--ticket-primary);
        box-shadow: 0 0 0 3px var(--ticket-focus);
        outline: 0;
    }

    #ticketDetailsRoot textarea.form-control {
        min-height: 132px;
        resize: vertical;
        line-height: 1.6;
    }

    #ticketDetailsRoot .btn-send-reply {
        align-items: center;
        display: inline-flex;
        font-size: 12.5px;
        justify-content: center;
        min-height: 37px;
        padding: 8px 16px;
        white-space: normal;
        transition: filter .15s ease, box-shadow .15s ease;
    }

    #ticketDetailsRoot .btn-send-reply:hover {
        filter: brightness(.97);
    }

    #ticketDetailsRoot .bn-digit,
    #ticketDetailsRoot .font-monospace {
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        #ticketDetailsRoot {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #ticketDetailsRoot .ticket-page-header {
            align-items: stretch !important;
            flex-direction: column;
            gap: 10px;
        }

        #ticketDetailsRoot .ticket-page-title {
            font-size: 16px;
        }

        #ticketDetailsRoot .header-back-btn,
        #ticketDetailsRoot .btn-send-reply {
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        #ticketDetailsRoot .ticket-card {
            padding: 15px;
        }

        #ticketDetailsRoot .ticket-section-heading {
            margin-bottom: 13px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #ticketDetailsRoot *,
        #ticketDetailsRoot *::before,
        #ticketDetailsRoot *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="ticketDetailsRoot">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center flex-wrap gap-1.5 ticket-page-title">
            <span>Ticket Details:</span>
            <span class="font-monospace text-primary bn-digit ticket-number">{{ $ticket->ticket_no }}</span>
        </h5>
        <a href="{{ route('admin.support.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-semibold d-inline-flex align-items-center text-nowrap shadow-xs header-back-btn">
            <i class="fas fa-arrow-left me-1.5"></i>
            <span>Back to List</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- Ticket Information Box -->
        <div class="col-12 col-md-5">
            <div class="ticket-card h-100">
                <h6 class="ticket-section-heading">Customer & Query Info</h6>

                <div class="ticket-meta-item">
                    <strong>Customer Name:</strong>
                    <span>{{ $ticket->user_name ?? 'N/A' }}</span>
                </div>

                <div class="ticket-meta-item">
                    <strong>Email / Phone:</strong>
                    <span class="font-monospace bn-digit">{{ $ticket->user_email ?? ($ticket->user_phone ?? 'N/A') }}</span>
                </div>

                <div class="ticket-meta-item">
                    <strong>Subject:</strong>
                    <span class="fw-semibold">{{ $ticket->subject }}</span>
                </div>

                <div class="ticket-meta-item mb-3">
                    <strong>Submitted At:</strong>
                    <span class="font-monospace bn-digit">{{ date('d M Y, h:i A', strtotime($ticket->created_at)) }}</span>
                </div>

                <div class="border-top pt-3">
                    <strong class="text-dark d-block mb-1.5 ticket-message-heading">User Message:</strong>
                    <div class="user-msg-box">
                        {{ $ticket->message ?? ($ticket->subject ?? 'No detailed description provided.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Reply & Status Update Box -->
        <div class="col-12 col-md-7">
            <div class="ticket-card h-100">
                <h6 class="ticket-section-heading">Admin Response & Status</h6>

                <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select fw-semibold">
                            <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="checked" {{ $ticket->status == 'checked' ? 'selected' : '' }}>Checked</option>
                            <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Reply Message</label>
                        <textarea name="admin_reply" rows="5" class="form-control" placeholder="Write your reply here...">{{ old('admin_reply', isset($ticket->admin_reply) ? $ticket->admin_reply : '') }}</textarea>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="btn btn-warning fw-bold text-dark shadow-xs btn-send-reply">
                            <i class="fas fa-paper-plane me-1.5"></i>
                            <span>Send Reply & Update Status</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection