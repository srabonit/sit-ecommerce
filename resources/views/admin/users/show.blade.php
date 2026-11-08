
@extends('layouts.admin')

@section('title', 'Profile: ' . $user->name)

@php
    // Universal dynamic image resolver for profile view
    $resolveProfilePhoto = function($path) {
        if (empty($path)) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    $userProfilePhoto = $resolveProfilePhoto($user->photo ?? null);
@endphp

@section('admin_content')

<style>
    /* =========================================================
       USER PROFILE - KAIADMIN LITE CLASSIC / WHITE CLASSIC
       All styles are scoped to #user-profile-root
    ========================================================= */

    #user-profile-root {
        --profile-primary: var(--bs-primary, #1572e8);
        --profile-success: var(--bs-success, #31ce36);
        --profile-danger: var(--bs-danger, #f25961);
        --profile-warning: var(--bs-warning, #ffad46);
        --profile-info: var(--bs-info, #48abf7);
        --profile-text: #1a2035;
        --profile-muted: #6b7280;
        --profile-border: #e5e7eb;
        --profile-surface: #ffffff;
        --profile-soft: #f8fafc;
        color: var(--profile-text);
        font-size: 13px;
    }

    /* Page header */
    #user-profile-root .user-show-header {
        padding: 2px 0 15px;
        border-bottom: 1px solid var(--profile-border);
    }

    #user-profile-root .user-show-header h4 {
        color: var(--profile-text);
        font-size: 19px !important;
        font-weight: 700;
        letter-spacing: -0.3px;
        line-height: 1.35;
    }

    #user-profile-root .user-show-header .badge {
        font-size: 10px !important;
        font-weight: 700;
        letter-spacing: .3px;
        border-radius: 5px;
    }

    /* Buttons and badges */
    #user-profile-root .btn,
    #user-profile-root .badge {
        border-radius: 5px !important;
    }

    #user-profile-root .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #user-profile-root .btn-sm {
        font-size: 11px;
    }

    #user-profile-root .header-back-btn {
        min-height: 34px;
        padding: 7px 14px !important;
    }

    #user-profile-root .header-back-btn:hover {
        transform: translateY(-1px);
    }

    /* Main cards */
    #user-profile-root .profile-card {
        background: var(--profile-surface);
        border: 1px solid var(--profile-border);
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    #user-profile-root .profile-card:hover {
        border-color: #d8dee8;
    }

    /* Profile identity card */
    #user-profile-root .profile-avatar {
        width: 100px;
        height: 100px;
        border: 3px solid #fff;
        outline: 1px solid var(--profile-border);
        background: #f3f4f6;
    }

    #user-profile-root .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #user-profile-root .profile-name {
        color: var(--profile-text);
        font-size: 16px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #user-profile-root .profile-designation {
        color: var(--profile-muted);
        font-size: 12px;
    }

    #user-profile-root .profile-contact {
        border-top: 1px solid var(--profile-border);
        padding-top: 12px;
        margin-top: 12px;
    }

    #user-profile-root .profile-contact p {
        line-height: 1.6;
    }

    /* Statistic cards */
    #user-profile-root .stat-summary-box {
        height: 100%;
        min-height: 86px;
        padding: 14px 12px;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        background: #fff;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #user-profile-root .stat-summary-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .055);
    }

    #user-profile-root .stat-summary-box .stat-label {
        display: block;
        margin-bottom: 7px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    #user-profile-root .stat-summary-box h5 {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
        line-height: 1.3;
    }

    #user-profile-root .stat-box-default {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    #user-profile-root .stat-box-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    #user-profile-root .stat-box-primary {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    #user-profile-root .stat-box-warning {
        background: #fffbeb;
        border-color: #fde68a;
    }

    #user-profile-root .stat-box-danger {
        background: #fef2f2;
        border-color: #fecaca;
    }

    #user-profile-root .stat-box-info {
        background: #f0f9ff;
        border-color: #bae6fd;
    }

    /* Section headings */
    #user-profile-root .profile-section-title {
        color: var(--profile-text);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.4;
    }

    /* Information fields */
    #user-profile-root .info-field-box {
        height: 100%;
        min-height: 64px;
        padding: 11px 13px;
        background: var(--profile-soft);
        border: 1px solid #edf0f3;
        border-radius: 6px;
        overflow-wrap: anywhere;
        transition: background .18s ease, border-color .18s ease;
    }

    #user-profile-root .info-field-box:hover {
        background: #f5f8fc;
        border-color: #dfe5ec;
    }

    #user-profile-root .info-field-label {
        display: block;
        margin-bottom: 4px;
        color: #7b8190;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .35px;
        line-height: 1.4;
        text-transform: uppercase;
    }

    #user-profile-root .info-field-value {
        display: block;
        color: var(--profile-text);
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    /* Tables */
    #user-profile-root .profile-data-table {
        width: 100%;
        min-width: 620px;
        margin-bottom: 0;
        vertical-align: middle;
    }

    #user-profile-root .profile-data-table thead th {
        padding: 11px 13px;
        background: #f8f9fc;
        color: #7b8190;
        border-top: 0;
        border-bottom: 1px solid var(--profile-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #user-profile-root .profile-data-table tbody td {
        padding: 11px 13px;
        color: #4b5563;
        border-bottom: 1px solid #f0f1f4;
        font-size: 12px;
        line-height: 1.5;
        vertical-align: middle;
    }

    #user-profile-root .profile-data-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #user-profile-root .profile-data-table tbody tr:hover {
        background: #fafbfe;
    }

    #user-profile-root .profile-data-table .badge {
        font-size: 9px !important;
        font-weight: 700;
        letter-spacing: .25px;
    }

    #user-profile-root .profile-data-table .btn {
        min-width: 48px;
    }

    #user-profile-root .profile-data-table a {
        text-decoration: none;
    }

    #user-profile-root .profile-data-table a:hover {
        text-decoration: none;
    }

    /* Table wrapper */
    #user-profile-root .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Employee documents */
    #user-profile-root .document-list .list-group-item {
        background: transparent;
        border-color: #f0f1f4;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    #user-profile-root .document-list .list-group-item:last-child {
        border-bottom: 0;
    }

    #user-profile-root .document-download-btn {
        flex-shrink: 0;
    }

    /* Numeric and monospace data */
    #user-profile-root .bn-digit,
    #user-profile-root .font-monospace {
        font-weight: 600;
    }

    /* Notes */
    #user-profile-root .profile-note {
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    /* Empty states */
    #user-profile-root .profile-empty-state {
        padding: 24px 12px !important;
        color: #9ca3af !important;
        font-size: 12px !important;
        text-align: center;
    }

    /* Mobile responsive */
    @media (max-width: 767.98px) {
        #user-profile-root {
            padding-top: 12px !important;
        }

        #user-profile-root .user-show-header {
            align-items: stretch !important;
            padding-bottom: 12px;
        }

        #user-profile-root .user-show-header h4 {
            font-size: 17px !important;
        }

        #user-profile-root .header-back-btn {
            width: 100%;
            justify-content: center;
            min-height: 36px;
        }

        #user-profile-root .profile-card {
            border-radius: 7px !important;
        }

        #user-profile-root .profile-avatar {
            width: 88px;
            height: 88px;
        }

        #user-profile-root .stat-summary-box {
            min-height: 76px;
            padding: 11px 8px;
        }

        #user-profile-root .stat-summary-box .stat-label {
            font-size: 10px;
        }

        #user-profile-root .stat-summary-box h5 {
            font-size: 16px;
        }

        #user-profile-root .info-field-box {
            min-height: 58px;
            padding: 10px;
        }

        #user-profile-root .profile-data-table thead th,
        #user-profile-root .profile-data-table tbody td {
            padding: 9px 10px;
        }
    }

    @media (max-width: 400px) {
        #user-profile-root .stat-summary-box h5 {
            font-size: 14px;
        }

        #user-profile-root .profile-card.p-4 {
            padding: 18px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="user-profile-root">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 user-show-header">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>

            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="badge {{ $user->role === 'customer' ? 'bg-info text-dark' : 'bg-primary text-white' }} text-capitalize px-2 py-1 fw-semibold">
                    {{ str_replace('_', ' ', $user->role) }}
                </span>

                <span class="text-muted small">
                    Registered By: <strong class="text-dark">{{ $creator ?? 'System/Self' }}</strong>
                </span>
            </div>
        </div>

        <a href="{{ route('admin.roles.users') }}"
           class="btn btn-outline-secondary btn-sm px-3 py-1 fw-semibold d-inline-flex align-items-center shadow-sm header-back-btn">
            <i class="fas fa-arrow-left me-2"></i>
            <span>Back to Users</span>
        </a>
    </div>

    <div class="row g-3">

        <!-- Left Column: Profile Information -->
        <div class="col-12 col-lg-4">

            <!-- Profile Identity Card -->
            <div class="profile-card p-4 text-center mb-3">

                <div class="profile-avatar mx-auto rounded-circle overflow-hidden mb-3">
                    @if(!empty($userProfilePhoto))
                        <img src="{{ $userProfilePhoto }}"
                             alt="{{ $user->name }}"
                             loading="lazy">
                    @else
                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-2x text-muted opacity-50"></i>
                        </div>
                    @endif
                </div>

                <h5 class="profile-name mb-1">{{ $user->name }}</h5>

                <p class="profile-designation mb-2">
                    {{ $user->role === 'customer' ? 'Registered Customer' : ($user->designation ?? 'Staff Member') }}
                </p>

                <div class="d-flex justify-content-center gap-1 mb-3">
                    <span class="badge {{ $user->is_locked ? 'bg-danger' : 'bg-success' }} px-3 py-2 fw-semibold">
                        <i class="fas {{ $user->is_locked ? 'fa-lock' : 'fa-check-circle' }} me-1"></i>
                        {{ $user->is_locked ? 'Locked' : 'Active' }}
                    </span>
                </div>

                <div class="profile-contact">
                    <p class="small text-dark mb-1 font-monospace text-break">
                        <i class="fas fa-envelope text-muted me-1"></i>
                        {{ $user->email }}
                    </p>

                    <p class="small text-muted mb-0 font-monospace bn-digit">
                        <i class="fas fa-phone-alt me-1"></i>
                        {{ $user->phone ?? 'No Phone' }}
                    </p>
                </div>
            </div>

            @if($user->role !== 'customer')
                <!-- Employee Documents -->
                <div class="profile-card p-3 mb-3 mb-lg-0">

                    <h6 class="profile-section-title border-bottom pb-3 mb-2 d-flex align-items-center">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        <span>Employee Documents</span>
                    </h6>

                    <ul class="list-group list-group-flush small document-list">
                        @forelse($documents as $doc)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 gap-2">
                                <span class="text-truncate text-dark fw-medium me-2"
                                      style="max-width: 170px;">
                                    <i class="far fa-file-alt text-muted me-1"></i>
                                    {{ $doc->doc_title }}
                                </span>

                                <a href="{{ route('admin.roles.users.download_doc', $doc->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold d-inline-flex align-items-center document-download-btn">
                                    <i class="fas fa-download me-1"></i>
                                    <span>Download</span>
                                </a>
                            </li>
                        @empty
                            <li class="list-group-item px-0 py-3 text-muted">
                                <i class="far fa-folder-open me-1"></i>
                                No documents uploaded.
                            </li>
                        @endforelse
                    </ul>
                </div>
            @endif

        </div>

        <!-- Right Column: Customer / Staff Details -->
        <div class="col-12 col-lg-8">

            @if($user->role === 'customer')

                <!-- Customer Order Statistics -->
                <div class="row g-2 mb-3">

                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-default text-center">
                            <span class="stat-label text-muted">Total Orders</span>
                            <h5 class="text-dark bn-digit">{{ $orderStats['total'] }}</h5>
                        </div>
                    </div>

                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-success text-center">
                            <span class="stat-label text-success">Completed Orders</span>
                            <h5 class="text-success bn-digit">{{ $orderStats['completed'] }}</h5>
                        </div>
                    </div>

                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-primary text-center">
                            <span class="stat-label text-primary">Pending Orders</span>
                            <h5 class="text-primary bn-digit">{{ $orderStats['pending'] }}</h5>
                        </div>
                    </div>

                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-warning text-center">
                            <span class="stat-label text-warning-emphasis">Returned Orders</span>
                            <h5 class="text-warning-emphasis bn-digit">{{ $orderStats['returned'] }}</h5>
                        </div>
                    </div>

                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-danger text-center">
                            <span class="stat-label text-danger">Cancelled Orders</span>
                            <h5 class="text-danger bn-digit">{{ $orderStats['cancelled'] }}</h5>
                        </div>
                    </div>

                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-info text-center">
                            <span class="stat-label text-info-emphasis">Total Purchases</span>
                            <h5 class="text-info-emphasis bn-digit">
                                {{ number_format($orderStats['spent'], 2) }} ৳
                            </h5>
                        </div>
                    </div>

                </div>

                <!-- Customer Address & Account Information -->
                <div class="profile-card p-3 mb-3">

                    <div class="row g-2">

                        <div class="col-12 col-md-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Delivery Address</span>
                                <span class="info-field-value">
                                    {{ $user->bio ?? 'No address set' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Account Created</span>
                                <span class="info-field-value bn-digit">
                                    {{ date('d M, Y', strtotime($user->created_at)) }}
                                </span>
                            </div>
                        </div>

                        @if($user->note)
                            <div class="col-12">
                                <div class="info-field-box">
                                    <span class="info-field-label">Admin Note</span>
                                    <span class="text-dark fw-medium profile-note">
                                        {{ $user->note }}
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Recent Order History -->
                <div class="profile-card p-0 overflow-hidden mb-3">

                    <div class="p-3 border-bottom bg-white">
                        <h6 class="profile-section-title mb-0 d-flex align-items-center">
                            <i class="fas fa-shopping-bag text-primary me-2"></i>
                            <span>Recent Orders</span>
                        </h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 text-nowrap profile-data-table">

                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Order Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($customerOrders as $ord)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.order.view', $ord->id) }}"
                                               class="fw-bold text-primary font-monospace bn-digit">
                                                #{{ $ord->invoice_no ?? $ord->id }}
                                            </a>
                                        </td>

                                        <td class="bn-digit text-muted">
                                            {{ date('d M Y', strtotime($ord->created_at)) }}
                                        </td>

                                        <td class="fw-bold font-monospace bn-digit text-dark">
                                            {{ number_format($ord->grand_total, 2) }} ৳
                                        </td>

                                        <td>
                                            <span class="badge bg-light text-dark border text-uppercase px-2 py-1">
                                                {{ $ord->payment_status ?? 'unpaid' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-light text-dark border text-uppercase px-2 py-1">
                                                {{ str_replace('_', ' ', $ord->order_status) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <a href="{{ route('admin.order.view', $ord->id) }}"
                                               class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="profile-empty-state">
                                            <i class="fas fa-shopping-bag fa-lg d-block mb-2 opacity-50"></i>
                                            No orders found for this customer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

                <!-- Customer Support Ticket History -->
                <div class="profile-card p-0 overflow-hidden">

                    <div class="p-3 border-bottom bg-white">
                        <h6 class="profile-section-title mb-0 d-flex align-items-center">
                            <i class="fas fa-headset text-danger me-2"></i>
                            <span>Support Tickets</span>
                        </h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 text-nowrap profile-data-table">

                            <thead>
                                <tr>
                                    <th>Ticket / Subject</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($supportTickets as $ticket)
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block">
                                                {{ $ticket->subject ?? 'No Subject' }}
                                            </strong>

                                            <small class="text-muted font-monospace bn-digit">
                                                #{{ $ticket->ticket_no ?? $ticket->id }}
                                            </small>
                                        </td>

                                        <td>{{ $ticket->category ?? 'General' }}</td>

                                        <td>
                                            <span class="badge {{ ($ticket->status ?? '') === 'closed' ? 'bg-secondary' : 'bg-success' }} px-2 py-1">
                                                {{ ucfirst($ticket->status ?? 'open') }}
                                            </span>
                                        </td>

                                        <td class="bn-digit text-muted">
                                            {{ date('d M Y', strtotime($ticket->created_at)) }}
                                        </td>

                                        <td class="text-end">
                                            @if(Route::has('admin.support.show'))
                                                <a href="{{ route('admin.support.show', $ticket->id) }}"
                                                   class="btn btn-sm btn-outline-dark py-1 px-2 fw-semibold">
                                                    Reply
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="profile-empty-state">
                                            <i class="fas fa-headset fa-lg d-block mb-2 opacity-50"></i>
                                            No support tickets submitted by this customer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

            @else

                <!-- Staff & Admin Employment Information -->
                <div class="profile-card p-3 p-md-4 mb-3">

                    <h6 class="profile-section-title border-bottom pb-3 mb-3 d-flex align-items-center">
                        <i class="fas fa-briefcase text-success me-2"></i>
                        <span>Employment & Salary Details</span>
                    </h6>

                    <div class="row g-2">

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Designation</span>
                                <span class="info-field-value">
                                    {{ $user->designation ?? 'Not Assigned' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Branch Assignment</span>
                                <span class="info-field-value bn-digit">
                                    {{ $user->branch_id ? 'Branch #' . $user->branch_id : 'Head Office / Online Store' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Joining Date</span>
                                <span class="info-field-value bn-digit">
                                    {{ $user->joining_date ?? 'Not Set' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Resignation / Closing Date</span>
                                <span class="info-field-value bn-digit">
                                    {{ $user->resignation_date ?? 'Currently Active' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Monthly Basic Salary</span>
                                <span class="info-field-value text-primary bn-digit">
                                    {{ number_format($user->monthly_salary ?? 0, 2) }} BDT
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Salary Pay Day</span>
                                <span class="info-field-value bn-digit">
                                    Every {{ $user->salary_pay_day ?? 5 }}th of Month
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">NID Number</span>
                                <span class="info-field-value font-monospace bn-digit">
                                    {{ $user->nid_number ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Emergency Phone</span>
                                <span class="info-field-value font-monospace bn-digit">
                                    {{ $user->phone_alt ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-field-box">
                                <span class="info-field-label">Reference Contact</span>
                                <span class="info-field-value bn-digit">
                                    {{ $user->reference_name ?? 'N/A' }}
                                    ({{ $user->reference_phone ?? 'N/A' }})
                                </span>
                            </div>
                        </div>

                    </div>

                    @if($user->bio)
                        <div class="mt-3 pt-3 border-top small">
                            <span class="info-field-label mb-1">Notes / Bio</span>
                            <p class="text-dark mb-0 profile-note">
                                {{ $user->bio }}
                            </p>
                        </div>
                    @endif

                </div>

            @endif

        </div>
    </div>
</div>

@endsection
