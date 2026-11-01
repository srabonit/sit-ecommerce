@extends('layouts.admin')

@section('title', 'Tax Payments & Treasury Challans')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 tax-payments-wrapper">
    <!-- Header & Action Controls -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2">
                    <i class="fas fa-receipt text-success"></i> Treasury Payments &amp; Challans
                </h4>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium font-monospace" style="font-size: 11px;">
                    {{ $taxPayments->total() }} Records
                </span>
            </div>
            <p class="text-secondary small mb-0">Manage NBR treasury challans, Mushak tax remittances, and government deposit records.</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <button type="button" class="btn btn-sm btn-success px-3 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                <i class="fas fa-plus-circle"></i> <span>New Payment</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border border-success-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-circle-check me-2 fs-6"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-circle-exclamation me-2 fs-6"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <span class="fw-semibold small">Please resolve the following errors:</span>
            </div>
            <ul class="mb-0 ps-3 small text-secondary">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Summary Cards -->
    <div class="row g-2 mb-3">
        <!-- 1. Verified Payments -->
        <div class="col-12 col-md-4">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Verified Treasury Payments</span>
                        <div class="fw-bold text-success metric-value font-monospace text-truncate mt-1">৳{{ number_format($totalPaidAmount ?? 0, 2) }}</div>
                    </div>
                    <div class="p-2 rounded-1 bg-success-subtle text-success flex-shrink-0">
                        <i class="fas fa-building-columns fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Pending Verification -->
        <div class="col-12 col-md-4">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Pending Verification</span>
                        <div class="fw-bold text-warning metric-value font-monospace text-truncate mt-1">৳{{ number_format($totalPendingAmount ?? 0, 2) }}</div>
                    </div>
                    <div class="p-2 rounded-1 bg-warning-subtle text-warning flex-shrink-0">
                        <i class="fas fa-hourglass-half fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Challans Count -->
        <div class="col-12 col-md-4">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Total Challans</span>
                        <div class="fw-bold text-dark metric-value font-monospace text-truncate mt-1">{{ $taxPayments->total() }} Records</div>
                    </div>
                    <div class="p-2 rounded-1 bg-primary-subtle text-primary flex-shrink-0">
                        <i class="fas fa-file-invoice fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Panel -->
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="card-body p-2 p-md-3">
            <form action="{{ route('admin.tax.payments.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search challan, Mushak, notes..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Tax Period -->
                <div class="col-12 col-sm-6 col-md-2">
                    <input type="text" name="tax_period" class="form-control form-control-sm" placeholder="Period (e.g. October 2026)" value="{{ request('tax_period') }}">
                </div>

                <!-- Payment Status -->
                <div class="col-6 col-md-2">
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('payment_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('payment_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Payment Method -->
                <div class="col-6 col-md-2">
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <option value="treasury_challan" {{ request('payment_method') === 'treasury_challan' ? 'selected' : '' }}>Treasury Challan (TR-6)</option>
                        <option value="e_payment" {{ request('payment_method') === 'e_payment' ? 'selected' : '' }}>e-Payment / iVAS</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Cheque</option>
                        <option value="online_portal" {{ request('payment_method') === 'online_portal' ? 'selected' : '' }}>NBR Online Portal</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="col-12 col-md-3 d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="fas fa-filter"></i> <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'tax_period', 'payment_status', 'payment_method', 'start_date', 'end_date']))
                        <a href="{{ route('admin.tax.payments.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters" aria-label="Reset Filters">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table Card -->
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 12.5px;">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-3 py-2" style="width: 45px;">#</th>
                        <th class="py-2">Payment Date</th>
                        <th class="py-2">Tax Period</th>
                        <th class="py-2">Challan / Mushak Ref</th>
                        <th class="py-2">Method &amp; Account</th>
                        <th class="text-end py-2">Amount</th>
                        <th class="text-center py-2">Challan Doc</th>
                        <th class="text-center py-2">Status</th>
                        <th class="py-2">Recorded By</th>
                        <th class="text-end pe-3 py-2" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxPayments as $index => $payment)
                        <tr>
                            <td class="ps-3 font-monospace text-secondary">{{ $taxPayments->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</div>
                                <span class="text-secondary" style="font-size: 11px;">{{ \Carbon\Carbon::parse($payment->payment_date)->diffForHumans() }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">
                                    {{ $payment->tax_period }}
                                </span>
                            </td>
                            <td>
                                @if($payment->challan_number)
                                    <div class="fw-semibold text-dark font-monospace" style="font-size: 12px;">
                                        <i class="fas fa-hashtag text-secondary me-1"></i>{{ $payment->challan_number }}
                                    </div>
                                @endif
                                @if($payment->mushak_reference)
                                    <span class="badge bg-light text-secondary border font-monospace px-1 mt-1" style="font-size: 10px;">
                                        {{ $payment->mushak_reference }}
                                    </span>
                                @endif
                                @if(!$payment->challan_number && !$payment->mushak_reference)
                                    <span class="text-secondary small">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark fw-medium">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
                                @if($payment->notes)
                                    <span class="text-secondary d-block text-truncate" style="max-width: 180px; font-size: 11px;" title="{{ $payment->notes }}">{{ $payment->notes }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success font-monospace fs-6">৳{{ number_format($payment->amount ?? 0, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if(!empty($payment->document_file))
                                    <a href="{{ route('admin.tax.payments.download', $payment->id) }}" class="btn btn-sm btn-light border py-1 px-2 d-inline-flex align-items-center gap-1" title="Download Challan">
                                        <i class="fas fa-file-pdf text-danger" style="font-size: 11px;"></i> <span style="font-size: 11px;">Challan</span>
                                    </a>
                                @else
                                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 10.5px;">No File</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($payment->payment_status === 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace">Completed</span>
                                @elseif($payment->payment_status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 font-monospace">Pending</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark d-block fw-medium small">{{ $payment->creator ? $payment->creator->name : 'Admin' }}</span>
                                <span class="text-secondary" style="font-size: 10.5px;">{{ !empty($payment->created_at) ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y') : 'N/A' }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info border-light-subtle py-1 px-2 view-payment-btn" data-id="{{ $payment->id }}" title="View Details" aria-label="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary border-light-subtle py-1 px-2 edit-payment-btn" data-id="{{ $payment->id }}" title="Edit" aria-label="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger border-light-subtle py-1 px-2 delete-payment-btn" data-id="{{ $payment->id }}" data-challan="{{ $payment->challan_number ?: $payment->id }}" title="Delete" aria-label="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-secondary">
                                    <i class="fas fa-receipt fs-2 opacity-50 mb-2 d-block"></i>
                                    <h6 class="fw-semibold text-dark">No Treasury Payments Found</h6>
                                    <p class="small mb-3">Record government VAT remittances and upload treasury verification challans.</p>
                                    <button type="button" class="btn btn-sm btn-success px-3" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                                        <i class="fas fa-plus-circle me-1"></i> Record First Payment
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($taxPayments->hasPages())
            <div class="card-footer bg-white border-0 py-2 px-3 d-flex justify-content-center justify-content-md-end">
                {{ $taxPayments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Create Tax Payment -->
<div class="modal fade" id="createPaymentModal" tabindex="-1" aria-labelledby="createPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="createPaymentModalLabel">
                    <i class="fas fa-plus-circle text-success"></i> Record Treasury Payment
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tax.payments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Tax Period <span class="text-danger">*</span></label>
                            <input type="text" name="tax_period" class="form-control form-control-sm" placeholder="e.g. October 2026, 2026-Q4" value="{{ date('F Y') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Amount (BDT) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-secondary">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control font-monospace" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                <option value="treasury_challan">Treasury Challan (TR-6)</option>
                                <option value="e_payment">e-Payment / iVAS Portal</option>
                                <option value="bank_transfer">Bank Transfer / Pay Order</option>
                                <option value="online_portal">NBR Online Portal</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Treasury Challan Number</label>
                            <input type="text" name="challan_number" class="form-control form-control-sm font-monospace" placeholder="TR-2026-10398">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Mushak / NBR Reference</label>
                            <input type="text" name="mushak_reference" class="form-control form-control-sm font-monospace" placeholder="MUS-6.3-REF-092">
                        </div>

                        @if(!empty($bankAccounts) && count($bankAccounts) > 0)
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Debited Bank Account</label>
                                <select name="bank_account_id" class="form-select form-select-sm">
                                    <option value="">None / External Source</option>
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ $acc->bank_name ?? 'Bank' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select form-select-sm" required>
                                <option value="completed" selected>Completed (Verified)</option>
                                <option value="pending">Pending Verification</option>
                                <option value="rejected">Rejected / Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Upload Challan Attachment (PDF/Image)</label>
                            <input type="file" name="document_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <span class="text-secondary d-block mt-1" style="font-size: 11px;">Allowed file types: PDF, JPG, PNG, WEBP (Max 5 MB).</span>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Administrative Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Enter bank code, branch remarks, or deposit notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-3 pb-3">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success px-3 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="fas fa-save"></i> <span>Save Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Tax Payment -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="editPaymentModalLabel">
                    <i class="fas fa-edit text-primary"></i> Edit Tax Payment Record
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaymentForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-3">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" id="editPaymentDate" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Tax Period <span class="text-danger">*</span></label>
                            <input type="text" name="tax_period" class="form-control form-control-sm" id="editTaxPeriod" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Amount (BDT) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-secondary">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control font-monospace" id="editAmount" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm" id="editPaymentMethod" required>
                                <option value="treasury_challan">Treasury Challan (TR-6)</option>
                                <option value="e_payment">e-Payment / iVAS Portal</option>
                                <option value="bank_transfer">Bank Transfer / Pay Order</option>
                                <option value="online_portal">NBR Online Portal</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Treasury Challan Number</label>
                            <input type="text" name="challan_number" class="form-control form-control-sm font-monospace" id="editChallanNumber">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Mushak / NBR Reference</label>
                            <input type="text" name="mushak_reference" class="form-control form-control-sm font-monospace" id="editMushakReference">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select form-select-sm" id="editPaymentStatus" required>
                                <option value="completed">Completed (Verified)</option>
                                <option value="pending">Pending Verification</option>
                                <option value="rejected">Rejected / Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Replace Challan Document (Optional)</label>
                            <input type="file" name="document_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <div id="editCurrentFileBox" class="mt-1 small"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Administrative Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" id="editNotes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-3 pb-3">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="fas fa-check-circle"></i> <span>Update Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: View Payment Details -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1" aria-labelledby="viewPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="viewPaymentModalLabel">
                    <i class="fas fa-file-invoice text-info"></i> Treasury Payment Details
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="viewPaymentModalBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-4 text-primary"></i></div>
            </div>
            <div class="modal-footer border-0 pt-0 px-3 pb-3">
                <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Global Hidden Delete Form -->
<form id="globalDeletePaymentForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
    /* Strict 5px Radius Rule */
    .tax-payments-wrapper .card,
    .tax-payments-wrapper .btn,
    .tax-payments-wrapper .form-control,
    .tax-payments-wrapper .form-select,
    .tax-payments-wrapper .input-group-text,
    .tax-payments-wrapper .badge,
    .tax-payments-wrapper .alert,
    .tax-payments-wrapper .rounded-1,
    .modal-content,
    .modal-content .btn,
    .modal-content .form-control,
    .modal-content .form-select,
    .modal-content .input-group-text,
    .modal-content .badge {
        border-radius: 5px !important;
    }

    /* Button Group Radius */
    .tax-payments-wrapper .btn-group .btn:first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
    }

    .tax-payments-wrapper .btn-group .btn:last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
    }

    /* Fluid Typographic Metric Value */
    .tax-payments-wrapper .metric-value {
        font-size: clamp(1.15rem, 1.8vw, 1.45rem);
        line-height: 1.2;
    }

    /* Responsive Mobile Optimization */
    @media (max-width: 575.98px) {
        .tax-payments-wrapper {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-payments-wrapper .metric-value {
            font-size: 1.15rem;
        }

        #createPaymentModal .modal-dialog,
        #editPaymentModal .modal-dialog,
        #viewPaymentModal .modal-dialog {
            margin: 0.5rem;
        }

        #createPaymentModal .modal-body,
        #editPaymentModal .modal-body,
        #viewPaymentModal .modal-body {
            padding: 0.75rem !important;
        }

        #viewPaymentModal .list-group-item.d-flex {
            gap: 0.75rem;
        }

        #viewPaymentModal .list-group-item.d-flex > :last-child {
            text-align: right;
            overflow-wrap: anywhere;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
        const viewModal = new bootstrap.Modal(document.getElementById('viewPaymentModal'));

        // Edit Payment Trigger
        document.querySelectorAll('.edit-payment-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;

                fetch(`{{ url('admin/tax/payments/show') }}/${id}`)
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) {
                            alert('Unable to load payment data.');
                            return;
                        }
                        const p = res.data;
                        document.getElementById('editPaymentDate').value = p.payment_date ? p.payment_date.split('T')[0] : '';
                        document.getElementById('editTaxPeriod').value = p.tax_period || '';
                        document.getElementById('editAmount').value = p.amount || '';
                        document.getElementById('editPaymentMethod').value = p.payment_method || 'treasury_challan';
                        document.getElementById('editChallanNumber').value = p.challan_number || '';
                        document.getElementById('editMushakReference').value = p.mushak_reference || '';
                        document.getElementById('editPaymentStatus').value = p.payment_status || 'completed';
                        document.getElementById('editNotes').value = p.notes || '';

                        const fileBox = document.getElementById('editCurrentFileBox');
                        if (p.document_file) {
                            fileBox.innerHTML = `<span class="badge bg-light text-primary border px-2 py-1"><i class="fas fa-file-circle-check me-1"></i> Current Challan Attached</span>`;
                        } else {
                            fileBox.innerHTML = '';
                        }

                        const form = document.getElementById('editPaymentForm');
                        form.action = `{{ url('admin/tax/payments/update') }}/${id}`;
                        editModal.show();
                    })
                    .catch(() => alert('Server error: failed to load payment details.'));
            });
        });

        // View Payment Details Trigger
        document.querySelectorAll('.view-payment-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const body = document.getElementById('viewPaymentModalBody');
                body.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-4 text-primary"></i></div>';
                viewModal.show();

                fetch(`{{ url('admin/tax/payments/show') }}/${id}`)
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) {
                            body.innerHTML = '<div class="text-danger text-center">Failed to load payment details.</div>';
                            return;
                        }
                        const p = res.data;
                        body.innerHTML = `
                            <div class="list-group list-group-flush" style="font-size: 12.5px;">
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Payment Date:</span>
                                    <span class="fw-semibold text-dark">${p.payment_date ? p.payment_date.split('T')[0] : 'N/A'}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Tax Period:</span>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${p.tax_period}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Remitted Amount:</span>
                                    <span class="fw-bold text-success font-monospace fs-6">৳${parseFloat(p.amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Challan Number:</span>
                                    <span class="font-monospace text-dark fw-semibold">${p.challan_number || 'N/A'}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Mushak Reference:</span>
                                    <span class="font-monospace text-dark">${p.mushak_reference || 'N/A'}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Method:</span>
                                    <span class="text-dark fw-medium">${p.payment_method ? p.payment_method.replace('_', ' ').toUpperCase() : 'N/A'}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Status:</span>
                                    <span class="badge bg-${p.payment_status === 'completed' ? 'success' : 'warning'}-subtle text-${p.payment_status === 'completed' ? 'success' : 'warning'} border px-2 py-1 font-monospace">${p.payment_status}</span>
                                </div>
                                ${p.document_file ? `
                                    <div class="list-group-item d-flex justify-content-between px-0 py-2 align-items-center">
                                        <span class="text-secondary">Challan Attachment:</span>
                                        <a href="{{ url('admin/tax/payments/download') }}/${p.id}" class="btn btn-sm btn-primary py-1 px-2 d-inline-flex align-items-center gap-1">
                                            <i class="fas fa-download"></i> <span>Download</span>
                                        </a>
                                    </div>
                                ` : ''}
                                ${p.notes ? `
                                    <div class="list-group-item px-0 py-2">
                                        <span class="text-secondary d-block mb-1">Administrative Notes:</span>
                                        <div class="p-2 bg-light border border-light-subtle rounded-1 text-dark small">${p.notes}</div>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    })
                    .catch(() => {
                        body.innerHTML = '<div class="text-danger text-center">Server error: failed to load payment details.</div>';
                    });
            });
        });

        // Delete Confirmation Trigger
        document.querySelectorAll('.delete-payment-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const challan = this.dataset.challan;

                if (confirm(`Are you sure you want to delete treasury payment record #${challan}?`)) {
                    const form = document.getElementById('globalDeletePaymentForm');
                    form.action = `{{ url('admin/tax/payments/delete') }}/${id}`;
                    form.submit();
                }
            });
        });
    });
</script>
@endsection