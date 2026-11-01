@extends('layouts.dashboard')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $suppSettingsCache = null;
    if ($suppSettingsCache === null) {
        $suppSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $suppSettings = $suppSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $suppCardBg        = $suppSettings['color_auth_card_bg'] ?? '#ffffff';
    $suppCardBorder    = $suppSettings['color_auth_card_border'] ?? '#e2e8f0';
    $suppBoxBg         = $suppSettings['color_site_bg'] ?? '#f8fafc';
    $suppTitleColor    = $suppSettings['color_page_title'] ?? '#0f172a';
    $suppTextColor     = $suppSettings['color_auth_text'] ?? '#64748b';
    $suppInputBg       = $suppSettings['color_auth_input_bg'] ?? '#ffffff';
    $suppInputBorder   = $suppSettings['color_auth_input_border'] ?? '#cbd5e1';
    $suppInputFocus    = $suppSettings['color_auth_input_focus_border'] ?? ($suppSettings['color_common_btn_bg'] ?? '#2563eb');
    
    $suppPrimaryColor  = $suppSettings['color_common_btn_bg'] ?? ($suppSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $suppPrimaryHover  = $suppSettings['color_auth_btn_hover'] ?? '#1d4ed8';

    // কন্টাক্ট ইনফো (সেটিংস থেকে অথবা বিদ্যমান ডিফল্ট)
    $suppHelpline      = $suppSettings['color_hdr_support_phone'] ?? ($suppSettings['footer_phone'] ?? '+880 1700-000000');
    $suppEmail         = $suppSettings['footer_email'] ?? 'info.srabonit@gmail.com';
@endphp

@section('title', 'Customer Support - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    :root {
        --supp-card-bg: {{ $suppCardBg }};
        --supp-card-border: {{ $suppCardBorder }};
        --supp-box-bg: {{ $suppBoxBg }};
        --supp-title: {{ $suppTitleColor }};
        --supp-text: {{ $suppTextColor }};
        --supp-input-bg: {{ $suppInputBg }};
        --supp-input-border: {{ $suppInputBorder }};
        --supp-input-focus: {{ $suppInputFocus }};
        --supp-primary: {{ $suppPrimaryColor }};
        --supp-primary-hover: {{ $suppPrimaryHover }};
    }

    /* Universal Strict Link Text Decoration */
    .support-content-root a,
    .support-content-root a:hover {
        text-decoration: none !important;
    }

    /* Main Container Card */
    .support-main-card {
        background-color: var(--supp-card-bg) !important;
        border: 1px solid var(--supp-card-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02) !important;
        padding: 24px;
        transition: all 0.25s ease;
    }

    .support-header-title {
        color: var(--supp-title) !important;
        font-weight: 700;
        font-size: 1.2rem;
        line-height: 1.35;
    }

    .support-header-subtitle {
        color: var(--supp-text) !important;
        font-size: 12.5px;
        line-height: 1.45;
    }

    /* Contact Info Tiles */
    .support-info-tile {
        background-color: var(--supp-box-bg) !important;
        border: 1px solid var(--supp-card-border) !important;
        border-radius: 14px;
        padding: 16px 14px;
        text-align: center;
        height: 100%;
        transition: all 0.2s ease;
    }

    .support-info-tile:hover {
        border-color: #cbd5e1 !important;
        transform: translateY(-2px);
    }

    .support-tile-title {
        color: var(--supp-title) !important;
        font-size: 13.5px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .support-tile-val {
        color: var(--supp-text) !important;
        font-size: 12.5px;
        line-height: 1.4;
        word-break: break-all;
    }

    /* Form Container Box */
    .support-form-box {
        background-color: var(--supp-box-bg) !important;
        border: 1px solid var(--supp-card-border) !important;
        border-radius: 16px;
        padding: 20px;
    }

    .support-form-label {
        color: var(--supp-title) !important;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 6px;
        display: block;
    }

    .support-form-control {
        background-color: var(--supp-input-bg) !important;
        border: 1px solid var(--supp-input-border) !important;
        color: var(--supp-title) !important;
        font-size: 13.5px;
        border-radius: 10px !important;
        padding: 9px 14px;
        transition: all 0.2s ease;
    }

    .support-form-control:focus {
        border-color: var(--supp-input-focus) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        background-color: #ffffff !important;
        outline: none;
    }

    .support-form-control::placeholder {
        color: #94a3b8 !important;
        font-size: 13px;
        opacity: 0.9;
    }

    /* Send Ticket Button */
    .btn-send-ticket {
        background-color: var(--supp-primary) !important;
        border-color: var(--supp-primary) !important;
        color: #ffffff !important;
        border-radius: 9999px !important;
        padding: 9px 28px !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.25) !important;
    }

    .btn-send-ticket:hover {
        background-color: var(--supp-primary-hover) !important;
        border-color: var(--supp-primary-hover) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(37, 99, 235, 0.35) !important;
    }

    .btn-send-ticket:active {
        transform: translateY(0);
    }

    /* Ticket History Table */
    .support-table-card {
        background: #ffffff !important;
        border: 1px solid var(--supp-card-border) !important;
        border-radius: 14px;
        overflow: hidden;
    }

    .support-history-table {
        margin-bottom: 0 !important;
        white-space: nowrap;
    }

    .support-history-table thead {
        background-color: var(--supp-box-bg) !important;
    }

    .support-history-table thead th {
        color: var(--supp-text) !important;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-bottom: 1px solid var(--supp-card-border);
        padding: 12px 14px;
    }

    .support-history-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--supp-card-border);
        font-size: 13px;
        color: var(--supp-text);
    }

    .support-history-table tbody tr:last-child td {
        border-bottom: none;
    }

    .ticket-badge-pill {
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 3px 8px !important;
        border-radius: 50px !important;
        letter-spacing: 0.02em;
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Small Screen Optimization (iPhone SE / 320px–575px) */
    @media (max-width: 575.98px) {
        .support-main-card {
            padding: 16px 14px !important;
            border-radius: 14px !important;
        }

        .support-header-title {
            font-size: 1.1rem !important;
        }

        .support-form-box {
            padding: 14px !important;
        }

        .support-form-control {
            padding: 8px 12px;
            font-size: 13px;
        }

        .btn-send-ticket {
            width: 100% !important;
            min-height: 44px;
        }

        .support-info-tile {
            padding: 12px 10px;
        }
    }
</style>

<div class="support-content-root">
    <div class="support-main-card">
        
        {{-- Header Bar --}}
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 border-bottom pb-3" style="border-color: var(--supp-card-border) !important;">
            <div>
                <h5 class="support-header-title mb-1">
                    <i class="fas fa-headset me-2" style="color: var(--supp-primary);"></i> Customer Support &amp; Help Desk
                </h5>
                <p class="support-header-subtitle mb-0">Select your query case, describe your problem, and submit directly to our management</p>
            </div>
        </div>

        {{-- Success Alert Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3 mb-md-4 py-2.5 px-3 d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2 fs-5 text-success"></i>
                <span class="small fw-semibold">{{ session('success') }}</span>
                <button type="button" class="btn-close shadow-none p-2.5" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Contact Info Row --}}
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-12 col-sm-4">
                <div class="support-info-tile">
                    <i class="fas fa-phone-alt fs-5 mb-2" style="color: var(--supp-primary);"></i>
                    <h6 class="support-tile-title">Helpline</h6>
                    <p class="support-tile-val bn-digit mb-0">{{ $suppHelpline }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="support-info-tile">
                    <i class="fas fa-envelope fs-5 text-success mb-2"></i>
                    <h6 class="support-tile-title">Email Support</h6>
                    <p class="support-tile-val mb-0">{{ $suppEmail }}</p>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="support-info-tile">
                    <i class="fas fa-clock fs-5 text-warning mb-2"></i>
                    <h6 class="support-tile-title">Working Hours</h6>
                    <p class="support-tile-val bn-digit mb-0">10:00 AM - 10:00 PM</p>
                </div>
            </div>
        </div>

        {{-- Support Ticket Submission Form --}}
        <div class="support-form-box mb-4 mb-md-5">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--supp-title); font-size: 14.5px;">
                <i class="fas fa-envelope-open-text" style="color: var(--supp-primary);"></i> 
                <span>Open a Support Ticket</span>
            </h6>

            <form action="{{ route('user.support.submit') }}" method="POST">
                @csrf
                
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="support-form-label">Case Category <span class="text-danger">*</span></label>
                        <select name="case_type" class="form-select support-form-control" required>
                            <option value="">-- Select Query Case --</option>
                            <option value="Order & Delivery">Order &amp; Delivery Inquiries</option>
                            <option value="Payment & Billing">Payment &amp; Transaction Issue</option>
                            <option value="Product Quality & Damage">Product Defect / Return Request</option>
                            <option value="Voucher & Discounts">Coupons &amp; Promo Codes</option>
                            <option value="Account & Security">Profile &amp; Account Safety</option>
                            <option value="General Question">General Feedback / Inquiries</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="support-form-label">Invoice / Order # (Optional)</label>
                        <input type="text" name="invoice_no" class="form-control support-form-control" placeholder="INV-20260904-QQFV">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="support-form-label">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control support-form-control" placeholder="Brief summary of your query" required>
                </div>

                <div class="mb-3">
                    <label class="support-form-label">Detailed Description <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control support-form-control" rows="4" placeholder="Explain your issue in detail so our team can resolve it promptly..." required></textarea>
                </div>

                <div class="text-start text-md-end pt-1">
                    <button type="submit" class="btn btn-send-ticket shadow-sm">
                        <i class="fas fa-paper-plane"></i> <span>Send Support Ticket</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- User Ticket History Section --}}
        <div>
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--supp-title); font-size: 15px;">
                <i class="fas fa-history" style="color: var(--supp-text);"></i> 
                <span>Your Support Ticket History</span>
            </h5>
            
            <div class="support-table-card">
                <div class="table-responsive">
                    <table class="table support-history-table align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">Ticket No</th>
                                <th>Subject</th>
                                <th class="text-center">Status</th>
                                <th>Admin Response / Reply</th>
                                <th class="text-end pe-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $userTickets = \Illuminate\Support\Facades\Schema::hasTable('support_tickets') 
                                    ? \Illuminate\Support\Facades\DB::table('support_tickets')->where('user_name', auth()->user()->name)->latest('id')->get() 
                                    : collect();
                            @endphp

                            @forelse($userTickets as $ut)
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold bn-digit" style="color: var(--supp-primary);">
                                        {{ $ut->ticket_no }}
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-truncate d-block" style="color: var(--supp-title); max-width: 240px;" title="{{ $ut->subject }}">
                                            {{ $ut->subject }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($ut->status == 'pending')
                                            <span class="badge bg-danger text-white ticket-badge-pill">Pending</span>
                                        @elseif($ut->status == 'checked')
                                            <span class="badge bg-warning text-dark ticket-badge-pill">Checked</span>
                                        @elseif($ut->status == 'resolved')
                                            <span class="badge bg-success text-white ticket-badge-pill">Resolved</span>
                                        @else
                                            <span class="badge bg-secondary text-white ticket-badge-pill">Closed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small d-block text-truncate" style="color: var(--supp-text); max-width: 280px;" title="{{ $ut->admin_reply ?? 'Awaiting management response...' }}">
                                            {{ $ut->admin_reply ?? 'Awaiting management response...' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 small bn-digit" style="color: var(--supp-text);">
                                        {{ date('d M Y, h:i A', strtotime($ut->created_at)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="color: var(--supp-text);">
                                        <i class="far fa-folder-open d-block fs-3 mb-1 opacity-50"></i>
                                        <span>You haven't submitted any support tickets yet.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection