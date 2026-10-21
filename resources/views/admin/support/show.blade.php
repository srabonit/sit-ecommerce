@extends('layouts.admin')

@section('title', 'Ticket Details - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #ticketDetailsRoot .btn,
    #ticketDetailsRoot .btn-sm,
    #ticketDetailsRoot button,
    #ticketDetailsRoot .form-control,
    #ticketDetailsRoot .form-select,
    #ticketDetailsRoot .user-msg-box {
        border-radius: 5px !important;
    }

    /* Enterprise Card Surface */
    #ticketDetailsRoot .ticket-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 20px;
    }

    .ticket-section-heading {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        padding-bottom: 10px;
        margin-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Clean Info Meta Rows */
    .ticket-meta-item {
        padding: 8px 10px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 5px;
        margin-bottom: 8px;
        font-size: 12.5px;
        color: #1e293b;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .ticket-meta-item strong {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 700;
    }

    .user-msg-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        font-size: 12.5px;
        color: #334155;
        line-height: 1.55;
        white-space: pre-line;
    }

    /* Form Controls */
    #ticketDetailsRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 5px;
    }

    #ticketDetailsRoot .form-control,
    #ticketDetailsRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #ticketDetailsRoot .form-control:focus,
    #ticketDetailsRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Submit Button */
    .btn-send-reply {
        font-size: 12.5px;
        padding: 8px 20px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        #ticketDetailsRoot .ticket-card {
            padding: 14px 16px;
        }
        .header-back-btn,
        .btn-send-reply {
            width: 100%;
            justify-content: center;
            text-align: center;
            padding: 8px 14px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="ticketDetailsRoot">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center flex-wrap gap-1.5" style="font-size: 17px;">
            <span>Ticket Details:</span>
            <span class="font-monospace text-primary bn-digit">{{ $ticket->ticket_no }}</span>
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
                    <strong class="text-dark d-block mb-1.5" style="font-size: 12px;">User Message:</strong>
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