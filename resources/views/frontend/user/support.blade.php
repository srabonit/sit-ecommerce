@extends('layouts.dashboard')

@section('title', 'Customer Support - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1"><i class="fas fa-headset text-primary me-2"></i> Customer Support & Help Desk</h5>
            <p class="text-muted small mb-0">Select your query case, describe your problem, and submit directly to our management</p>
        </div>
    </div>

    <!-- Success Alert Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 text-center h-100">
                <i class="fas fa-phone-alt fs-4 text-primary mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Helpline</h6>
                <p class="text-secondary small mb-0">+880 1700-000000</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 text-center h-100">
                <i class="fas fa-envelope fs-4 text-success mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Email Support</h6>
                <p class="text-secondary small mb-0">info.srabonit@gmail.com</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 text-center h-100">
                <i class="fas fa-clock fs-4 text-warning mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Working Hours</h6>
                <p class="text-secondary small mb-0">10:00 AM - 10:00 PM</p>
            </div>
        </div>
    </div>

    <div class="p-4 border rounded-4 bg-light mb-5">
        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-envelope-open-text text-primary me-2"></i> Open a Support Ticket</h6>

        <form action="{{ route('user.support.submit') }}" method="POST">
            @csrf
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Case Category <span class="text-danger">*</span></label>
                    <select name="case_type" class="form-select rounded-3 py-2" required>
                        <option value="">-- Select Query Case --</option>
                        <option value="Order & Delivery">Order & Delivery Inquiries</option>
                        <option value="Payment & Billing">Payment & Transaction Issue</option>
                        <option value="Product Quality & Damage">Product Defect / Return Request</option>
                        <option value="Voucher & Discounts">Coupons & Promo Codes</option>
                        <option value="Account & Security">Profile & Account Safety</option>
                        <option value="General Question">General Feedback / Inquiries</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Invoice / Order # (Optional)</label>
                    <input type="text" name="invoice_no" class="form-control rounded-3 py-2" placeholder=" INV-20260904-QQFV">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control rounded-3 py-2" placeholder="Brief summary of your query" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Detailed Description <span class="text-danger">*</span></label>
                <textarea name="message" class="form-control rounded-3 p-3" rows="4" placeholder="Explain your issue in detail so our team can resolve it promptly..." required></textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="fas fa-paper-plane me-1"></i> Send Support Ticket
                </button>
            </div>
        </form>
    </div>

    <!-- User Ticket History Section -->
    <div>
        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-history text-secondary me-2"></i> Your Support Ticket History</h5>
        <div class="card border shadow-none rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light small text-uppercase text-muted">
                        <tr>
                            <th class="ps-3">Ticket No</th>
                            <th>Subject</th>
                            <th>Status</th>
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
                                <td class="ps-3 font-monospace fw-bold text-primary">{{ $ut->ticket_no }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $ut->subject }}</span>
                                </td>
                                <td>
                                    @if($ut->status == 'pending')
                                        <span class="badge bg-danger">Pending</span>
                                    @elseif($ut->status == 'checked')
                                        <span class="badge bg-warning text-dark">Checked</span>
                                    @elseif($ut->status == 'resolved')
                                        <span class="badge bg-success">Resolved</span>
                                    @else
                                        <span class="badge bg-secondary">Closed</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $ut->admin_reply ?? 'Awaiting management response...' }}</span>
                                </td>
                                <td class="text-end pe-3 small text-muted">{{ date('d M Y, h:i A', strtotime($ut->created_at)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">You haven't submitted any support tickets yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection