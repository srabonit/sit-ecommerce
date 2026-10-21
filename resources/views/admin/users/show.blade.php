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
    /* Global Strict 5px Radius for Buttons & Badges */
    #user-profile-root .btn,
    #user-profile-root .btn-sm,
    #user-profile-root .btn-xs,
    #user-profile-root .badge {
        border-radius: 5px !important;
    }

    /* Enterprise Card Surface */
    #user-profile-root .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Order Stat Boxes */
    #user-profile-root .stat-summary-box {
        border-radius: 8px !important;
        padding: 14px 12px;
        border: 1px solid #e2e8f0;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    #user-profile-root .stat-summary-box:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
    }

    .stat-box-default { background: #f8fafc; border-color: #e2e8f0 !important; }
    .stat-box-success { background: #f0fdf4; border-color: #bbf7d0 !important; }
    .stat-box-primary { background: #eff6ff; border-color: #bfdbfe !important; }
    .stat-box-warning { background: #fefce8; border-color: #fef08a !important; }
    .stat-box-danger  { background: #fef2f2; border-color: #fecaca !important; }
    .stat-box-info    { background: #f0f9ff; border-color: #bae6fd !important; }

    /* Tables */
    .profile-data-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 12px;
        white-space: nowrap;
    }
    .profile-data-table td {
        padding: 9px 12px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Info Field Styling */
    .info-field-box {
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        height: 100%;
    }
    .info-field-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        display: block;
        margin-bottom: 2px;
    }
    .info-field-value {
        font-size: 13px;
        color: #0f172a;
        font-weight: 700;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .user-show-header h4 {
            font-size: 1.1rem;
        }
        .header-back-btn {
            width: 100%;
            text-align: center;
            justify-content: center;
            padding: 7px 12px !important;
        }
        #user-profile-root .stat-summary-box {
            padding: 10px 8px;
        }
        #user-profile-root .stat-summary-box h5 {
            font-size: 15px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="user-profile-root">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 user-show-header">
        <div>
            <h4 class="fw-bold text-dark mb-1" style="font-size: 18px;">{{ $user->name }}</h4>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="badge {{ $user->role === 'customer' ? 'bg-info text-dark' : 'bg-primary text-white' }} text-capitalize px-2.5 py-1 fw-semibold" style="font-size: 10.5px;">
                    {{ str_replace('_', ' ', $user->role) }}
                </span>
                <span class="text-muted small" style="font-size: 12px;">
                    Registered By: <strong class="text-dark">{{ $creator ?? 'System/Self' }}</strong>
                </span>
            </div>
        </div>
        <a href="{{ route('admin.roles.users') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center shadow-xs header-back-btn">
            <i class="fas fa-arrow-left me-1"></i> <span>Back</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- Left Column: Basic Profile Card -->
        <div class="col-12 col-lg-4">
            <div class="profile-card p-4 text-center mb-3">
                <div class="mx-auto rounded-circle overflow-hidden mb-3 border shadow-xs" style="width: 96px; height: 96px;">
                    @if(!empty($userProfilePhoto))
                        <img src="{{ $userProfilePhoto }}" class="w-100 h-100 object-fit-cover" alt="{{ $user->name }}" loading="lazy">
                    @else
                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-2x text-muted opacity-50"></i>
                        </div>
                    @endif
                </div>
                <h5 class="fw-bold text-dark mb-0.5" style="font-size: 16px;">{{ $user->name }}</h5>
                <p class="text-muted small mb-2.5" style="font-size: 12px;">
                    {{ $user->role === 'customer' ? 'Registered Customer' : ($user->designation ?? 'Staff Member') }}
                </p>
                <div class="d-flex justify-content-center gap-1 mb-3">
                    <span class="badge {{ $user->is_locked ? 'bg-danger' : 'bg-success' }} px-2.5 py-1 fw-semibold" style="font-size: 10px;">
                        {{ $user->is_locked ? 'Locked' : 'Active' }}
                    </span>
                </div>
                <div class="pt-2.5 border-top">
                    <p class="small text-dark mb-1 font-monospace text-break" style="font-size: 12px;">{{ $user->email }}</p>
                    <p class="small text-muted mb-0 font-monospace bn-digit" style="font-size: 12px;">{{ $user->phone ?? 'No Phone' }}</p>
                </div>
            </div>

            @if($user->role !== 'customer')
                <!-- Staff Documents -->
                <div class="profile-card p-3 mb-3 mb-lg-0">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2.5 d-flex align-items-center" style="font-size: 13.5px;">
                        <i class="fas fa-file-alt text-primary me-2"></i> <span>Employee Documents</span>
                    </h6>
                    <ul class="list-group list-group-flush small">
                        @forelse($documents as $doc)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-light-subtle">
                                <span class="text-truncate text-dark fw-medium me-2" style="max-width: 170px; font-size: 12px;">{{ $doc->doc_title }}</span>
                                <a href="{{ route('admin.roles.users.download_doc', $doc->id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5 text-nowrap fw-semibold d-inline-flex align-items-center" style="font-size: 11px;">
                                    <i class="fas fa-external-link-alt me-2"></i> <span>Download</span>
                                </a>
                            </li>
                        @empty
                            <li class="list-group-item px-0 py-2 text-muted" style="font-size: 12px;">No documents uploaded.</li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-12 col-lg-8">
            @if($user->role === 'customer')
                <!-- 1. Customer Order Summary Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-default text-center h-100">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; font-weight: 600;">Total Orders</span>
                            <h5 class="fw-bold mb-0 text-dark bn-digit">{{ $orderStats['total'] }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-success text-center h-100">
                            <span class="text-success small d-block mb-1" style="font-size: 11px; font-weight: 600;">Completed Orders</span>
                            <h5 class="fw-bold mb-0 text-success bn-digit">{{ $orderStats['completed'] }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-primary text-center h-100">
                            <span class="text-primary small d-block mb-1" style="font-size: 11px; font-weight: 600;">Pending Orders</span>
                            <h5 class="fw-bold mb-0 text-primary bn-digit">{{ $orderStats['pending'] }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-warning text-center h-100">
                            <span class="text-warning-emphasis small d-block mb-1" style="font-size: 11px; font-weight: 600;">Returned Orders</span>
                            <h5 class="fw-bold mb-0 text-warning-emphasis bn-digit">{{ $orderStats['returned'] }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-danger text-center h-100">
                            <span class="text-danger small d-block mb-1" style="font-size: 11px; font-weight: 600;">Cancelled Orders</span>
                            <h5 class="fw-bold mb-0 text-danger bn-digit">{{ $orderStats['cancelled'] }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="stat-summary-box stat-box-info text-center h-100">
                            <span class="text-info-emphasis small d-block mb-1" style="font-size: 11px; font-weight: 600;">Total Purchases</span>
                            <h5 class="fw-bold mb-0 text-info-emphasis bn-digit">{{ number_format($orderStats['spent'], 2) }} ৳</h5>
                        </div>
                    </div>
                </div>

                <!-- Customer Address & Note -->
                <div class="profile-card p-3 mb-3">
                    <div class="row g-2.5 small">
                        <div class="col-12 col-md-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Delivery Address</span>
                                <span class="info-field-value">{{ $user->bio ?? 'No address set' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Account Created</span>
                                <span class="info-field-value bn-digit">{{ date('d M, Y', strtotime($user->created_at)) }}</span>
                            </div>
                        </div>
                        @if($user->note)
                            <div class="col-12">
                                <div class="info-field-box">
                                    <span class="info-field-label">Admin Note:</span>
                                    <span class="text-dark fw-medium" style="font-size: 12.5px;">{{ $user->note }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 2. Recent Order History -->
                <div class="profile-card p-0 overflow-hidden mb-3">
                    <div class="p-3 border-bottom bg-white">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 13.5px;">
                            <i class="fas fa-shopping-bag text-primary me-2"></i> <span>Recent Orders</span>
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
                                            <a href="{{ route('admin.order.view', $ord->id) }}" class="fw-bold text-primary font-monospace text-decoration-none bn-digit">
                                                #{{ $ord->invoice_no ?? $ord->id }}
                                            </a>
                                        </td>
                                        <td class="bn-digit text-muted" style="font-size: 12px;">{{ date('d M Y', strtotime($ord->created_at)) }}</td>
                                        <td class="fw-bold font-monospace bn-digit">{{ number_format($ord->grand_total, 2) }} ৳</td>
                                        <td><span class="badge bg-light text-dark border text-uppercase px-2 py-0.5" style="font-size: 9.5px;">{{ $ord->payment_status ?? 'unpaid' }}</span></td>
                                        <td><span class="badge bg-light text-dark border text-uppercase px-2 py-0.5" style="font-size: 9.5px;">{{ str_replace('_', ' ', $ord->order_status) }}</span></td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.order.view', $ord->id) }}" class="btn btn-sm btn-outline-primary py-0.5 px-2.5 fw-semibold" style="font-size: 11px;">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-muted text-center py-4 small">No orders found for this customer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Customer Support Ticket History -->
                <div class="profile-card p-0 overflow-hidden">
                    <div class="p-3 border-bottom bg-white">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 13.5px;">
                            <i class="fas fa-headset text-danger me-2"></i> <span>Support Tickets</span>
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
                                            <strong class="text-dark d-block" style="font-size: 12.5px;">{{ $ticket->subject ?? 'No Subject' }}</strong>
                                            <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">#{{ $ticket->ticket_no ?? $ticket->id }}</small>
                                        </td>
                                        <td>{{ $ticket->category ?? 'General' }}</td>
                                        <td>
                                            <span class="badge {{ ($ticket->status ?? '') === 'closed' ? 'bg-secondary' : 'bg-success' }} px-2 py-0.5" style="font-size: 9.5px;">
                                                {{ ucfirst($ticket->status ?? 'open') }}
                                            </span>
                                        </td>
                                        <td class="bn-digit text-muted" style="font-size: 12px;">{{ date('d M Y', strtotime($ticket->created_at)) }}</td>
                                        <td class="text-end">
                                            @if(Route::has('admin.support.show'))
                                                <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn btn-sm btn-outline-dark py-0.5 px-2.5 fw-semibold" style="font-size: 11px;">Reply</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted text-center py-4 small">No support tickets submitted by this customer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <!-- Staff & Admin Info -->
                <div class="profile-card p-3 p-md-4 mb-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2.5 mb-3 d-flex align-items-center" style="font-size: 14px;">
                        <i class="fas fa-briefcase text-success me-2"></i> <span>Employment & Salary Details</span>
                    </h6>
                    <div class="row g-2.5 small">
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Designation</span>
                                <span class="info-field-value">{{ $user->designation ?? 'Not Assigned' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Branch Assignment</span>
                                <span class="info-field-value bn-digit">{{ $user->branch_id ? 'Branch #' . $user->branch_id : 'Head Office / Online Store' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Joining Date</span>
                                <span class="info-field-value bn-digit">{{ $user->joining_date ?? 'Not Set' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Resignation / Closing Date</span>
                                <span class="info-field-value bn-digit">{{ $user->resignation_date ?? 'Currently Active' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Monthly Basic Salary</span>
                                <span class="info-field-value text-primary bn-digit">{{ number_format($user->monthly_salary ?? 0, 2) }} BDT</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Salary Pay Day</span>
                                <span class="info-field-value bn-digit">Every {{ $user->salary_pay_day ?? 5 }}th of Month</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">NID Number</span>
                                <span class="info-field-value font-monospace bn-digit">{{ $user->nid_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="info-field-box">
                                <span class="info-field-label">Emergency Phone</span>
                                <span class="info-field-value font-monospace bn-digit">{{ $user->phone_alt ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-field-box">
                                <span class="info-field-label">Reference Contact</span>
                                <span class="info-field-value bn-digit">{{ $user->reference_name ?? 'N/A' }} ({{ $user->reference_phone ?? 'N/A' }})</span>
                            </div>
                        </div>
                    </div>
                    @if($user->bio)
                        <div class="mt-3 pt-3 border-top small">
                            <span class="info-field-label mb-1">Notes / Bio:</span>
                            <p class="text-dark mb-0" style="font-size: 12.5px; line-height: 1.5;">{{ $user->bio }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection