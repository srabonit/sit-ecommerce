@extends('layouts.admin')

@section('title', 'Tax Payments & Treasury Challans')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-3 tax-payments-wrapper">

 {{-- Page Header --}}
<div class="tp-page-header d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="tp-title-icon d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #dcfce7; color: #15803d; border-radius: 8px; font-size: 16px;">
            <i class="fas fa-receipt"></i>
        </span>
        <div class="min-w-0 lh-sm">
            <h4 class="tp-page-title fw-bold mb-1 text-dark text-truncate" style="font-size: 16px;">
                Tax Payments & Records
            </h4>
            <div class="tp-page-description text-secondary text-truncate m-0 p-0" style="font-size: 11.5px;">
                Manage Tax Payments & Treasury Records
            </div>
        </div>
    </div>

    <div class="tp-header-actions d-flex align-items-center gap-2 flex-shrink-0">
        <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary tp-btn d-inline-flex align-items-center" style="height: 34px;">
            <i class="fas fa-arrow-left me-1"></i>
            Dashboard
        </a>

        <button type="button"
                class="btn btn-sm btn-success tp-btn tp-primary-btn d-inline-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#createPaymentModal" style="height: 34px;">
            <i class="fas fa-plus me-1"></i>
            New Payment
        </button>
    </div>
</div>

    {{-- Alert Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show tp-alert mb-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-circle-check me-2"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show tp-alert mb-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-circle-exclamation me-2"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show tp-alert mb-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-triangle-exclamation"></i>
                <span class="fw-semibold small">Please resolve the following errors:</span>
            </div>
            <ul class="mb-0 ps-4 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">

        {{-- Verified Payments --}}
        <div class="col-12 col-md-4">
            <div class="card tp-card tp-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div class="tp-metric-content">
                            <span class="tp-metric-label">Verified Treasury Payments</span>
                            <div class="tp-metric-value text-success">
                                ৳{{ number_format($totalPaidAmount ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="tp-metric-icon tp-icon-success">
                            <i class="fas fa-building-columns"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Verification --}}
        <div class="col-12 col-md-4">
            <div class="card tp-card tp-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div class="tp-metric-content">
                            <span class="tp-metric-label">Pending Verification</span>
                            <div class="tp-metric-value text-warning">
                                ৳{{ number_format($totalPendingAmount ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="tp-metric-icon tp-icon-warning">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Challans --}}
        <div class="col-12 col-md-4">
            <div class="card tp-card tp-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div class="tp-metric-content">
                            <span class="tp-metric-label">Total Challans</span>
                            <div class="tp-metric-value text-dark">
                                {{ $taxPayments->total() }} Records
                            </div>
                        </div>
                        <div class="tp-metric-icon tp-icon-primary">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card tp-card tp-filter-card mb-4">
        <div class="card-header tp-card-header">
            <div class="d-flex align-items-center gap-2">
                <span class="tp-section-icon">
                    <i class="fas fa-sliders"></i>
                </span>
                <h6 class="mb-0 tp-section-title">Payment Filters</h6>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.tax.payments.index') }}" method="GET" class="row g-3 align-items-end">

                {{-- Search --}}
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label tp-form-label">Search</label>
                    <div class="input-group tp-input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search challan, Mushak, notes..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Tax Period --}}
                <div class="col-12 col-sm-6 col-xl-2">
                    <label class="form-label tp-form-label">Tax Period</label>
                    <input type="text"
                           name="tax_period"
                           class="form-control"
                           placeholder="October 2026"
                           value="{{ request('tax_period') }}">
                </div>

                {{-- Payment Status --}}
                <div class="col-6 col-sm-6 col-xl-2">
                    <label class="form-label tp-form-label">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('payment_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('payment_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                {{-- Payment Method --}}
                <div class="col-6 col-sm-6 col-xl-3">
                    <label class="form-label tp-form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="treasury_challan" {{ request('payment_method') === 'treasury_challan' ? 'selected' : '' }}>Treasury Challan (TR-6)</option>
                        <option value="e_payment" {{ request('payment_method') === 'e_payment' ? 'selected' : '' }}>e-Payment / iVAS</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Cheque</option>
                        <option value="online_portal" {{ request('payment_method') === 'online_portal' ? 'selected' : '' }}>NBR Online Portal</option>
                    </select>
                </div>

                {{-- Filter Actions --}}
                <div class="col-12 col-xl-2">
                    <label class="form-label tp-form-label d-none d-xl-block">&nbsp;</label>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary tp-btn flex-grow-1">
                            <i class="fas fa-filter me-1"></i>
                            Filter
                        </button>

                        @if(request()->hasAny(['search', 'tax_period', 'payment_status', 'payment_method', 'start_date', 'end_date']))
                            <a href="{{ route('admin.tax.payments.index') }}"
                               class="btn btn-outline-secondary tp-btn tp-reset-btn"
                               title="Reset Filters"
                               aria-label="Reset Filters">
                                <i class="fas fa-redo"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="card tp-card tp-table-card mb-4">
        <div class="card-header tp-card-header">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="tp-section-icon">
                        <i class="fas fa-receipt"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 tp-section-title">Payment Records</h6>
                        <small class="text-muted">Payment history and challan</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive tp-table-responsive">
            <table class="table table-hover align-middle mb-0 tp-table">
                <thead>
                    <tr>
                        <th class="ps-3 tp-col-number">#</th>
                        <th>Payment Date</th>
                        <th>Tax Period</th>
                        <th>Challan / Mushak Ref</th>
                        <th>Method &amp; Account</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Challan Doc</th>
                        <th class="text-center">Status</th>
                        <th>Recorded By</th>
                        <th class="text-end pe-3 tp-col-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($taxPayments as $index => $payment)
                        <tr>
                            <td class="ps-3 text-muted font-monospace">
                                {{ $taxPayments->firstItem() + $index }}
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                </div>
                                <span class="tp-table-subtext">
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->diffForHumans() }}
                                </span>
                            </td>

                            <td>
                                <span class="badge tp-period-badge">
                                    {{ $payment->tax_period }}
                                </span>
                            </td>

                            <td>
                                @if($payment->challan_number)
                                    <div class="fw-semibold text-dark font-monospace tp-reference">
                                        <i class="fas fa-hashtag text-muted me-1"></i>{{ $payment->challan_number }}
                                    </div>
                                @endif

                                @if($payment->mushak_reference)
                                    <span class="badge tp-reference-badge mt-1">
                                        {{ $payment->mushak_reference }}
                                    </span>
                                @endif

                                @if(!$payment->challan_number && !$payment->mushak_reference)
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>

                            <td>
                                <div class="text-dark fw-medium">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                </div>
                                @if($payment->notes)
                                    <span class="text-muted d-block text-truncate tp-notes-text"
                                          title="{{ $payment->notes }}">
                                        {{ $payment->notes }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <span class="fw-bold text-success font-monospace tp-amount">
                                    ৳{{ number_format($payment->amount ?? 0, 2) }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if(!empty($payment->document_file))
                                    <a href="{{ route('admin.tax.payments.download', $payment->id) }}"
                                       class="btn btn-sm btn-light border tp-document-btn"
                                       title="Download Challan">
                                        <i class="fas fa-file-pdf text-danger me-1"></i>
                                        Challan
                                    </a>
                                @else
                                    <span class="badge tp-no-file-badge">No File</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($payment->payment_status === 'completed')
                                    <span class="badge tp-status-badge tp-status-completed">Completed</span>
                                @elseif($payment->payment_status === 'pending')
                                    <span class="badge tp-status-badge tp-status-pending">Pending</span>
                                @else
                                    <span class="badge tp-status-badge tp-status-rejected">Rejected</span>
                                @endif
                            </td>

                            <td>
                                <span class="text-dark d-block fw-medium small">
                                    {{ $payment->creator ? $payment->creator->name : 'Admin' }}
                                </span>
                                <span class="tp-table-subtext">
                                    {{ !empty($payment->created_at) ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y') : 'N/A' }}
                                </span>
                            </td>

                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm tp-action-group">
                                    <button type="button"
                                            class="btn btn-outline-info view-payment-btn"
                                            data-id="{{ $payment->id }}"
                                            title="View Details"
                                            aria-label="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-primary edit-payment-btn"
                                            data-id="{{ $payment->id }}"
                                            title="Edit"
                                            aria-label="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-danger delete-payment-btn"
                                            data-id="{{ $payment->id }}"
                                            data-challan="{{ $payment->challan_number ?: $payment->id }}"
                                            title="Delete"
                                            aria-label="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="tp-empty-state">
                                    <div class="tp-empty-icon">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <h6 class="fw-semibold text-dark mb-2">No Treasury Payments Found</h6>
                                    <p class="text-muted small mb-3">
                                        Record VAT remittances and upload treasury challans.
                                    </p>
                                    <button type="button"
                                            class="btn btn-success tp-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#createPaymentModal">
                                        <i class="fas fa-plus me-1"></i>
                                        Record First Payment
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($taxPayments->hasPages())
            <div class="card-footer tp-pagination-footer">
                {{ $taxPayments->links() }}
            </div>
        @endif
    </div>
</div>


{{-- Create Tax Payment Modal --}}
<div class="modal fade tp-modal" id="createPaymentModal" tabindex="-1" aria-labelledby="createPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content tp-modal-content">
            <div class="modal-header tp-modal-header">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="createPaymentModalLabel">
                    <span class="tp-modal-icon tp-icon-success">
                        <i class="fas fa-plus-circle"></i>
                    </span>
                    Record Treasury Payment
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.tax.payments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body tp-modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Tax Period <span class="text-danger">*</span></label>
                            <input type="text" name="tax_period" class="form-control" placeholder="October 2026, 2026-Q4" value="{{ date('F Y') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Amount (BDT) <span class="text-danger">*</span></label>
                            <div class="input-group tp-input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control font-monospace" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="treasury_challan">Treasury Challan (TR-6)</option>
                                <option value="e_payment">e-Payment / iVAS Portal</option>
                                <option value="bank_transfer">Bank Transfer / Pay Order</option>
                                <option value="online_portal">NBR Online Portal</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Treasury Challan Number</label>
                            <input type="text" name="challan_number" class="form-control font-monospace" placeholder="TR-2026-10398">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Mushak / NBR Reference</label>
                            <input type="text" name="mushak_reference" class="form-control font-monospace" placeholder="MUS-6.3-REF-092">
                        </div>

                        @if(!empty($bankAccounts) && count($bankAccounts) > 0)
                            <div class="col-12 col-md-6">
                                <label class="form-label tp-form-label">Debited Bank Account</label>
                                <select name="bank_account_id" class="form-select">
                                    <option value="">None / External Source</option>
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ $acc->bank_name ?? 'Bank' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" required>
                                <option value="completed" selected>Completed (Verified)</option>
                                <option value="pending">Pending Verification</option>
                                <option value="rejected">Rejected / Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label tp-form-label">Upload Challan Attachment (PDF/Image)</label>
                            <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <span class="tp-help-text">
                                Allowed file types: PDF, JPG, PNG, WEBP (Max 5 MB).
                            </span>
                        </div>

                        <div class="col-12">
                            <label class="form-label tp-form-label">Administrative Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Enter bank code, branch remarks, or deposit notes..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer tp-modal-footer">
                    <button type="button" class="btn btn-light border tp-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success tp-btn">
                        <i class="fas fa-save me-1"></i>
                        Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Edit Tax Payment Modal --}}
<div class="modal fade tp-modal" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content tp-modal-content">
            <div class="modal-header tp-modal-header">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="editPaymentModalLabel">
                    <span class="tp-modal-icon tp-icon-primary">
                        <i class="fas fa-edit"></i>
                    </span>
                    Edit Tax Payment Record
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editPaymentForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-body tp-modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" id="editPaymentDate" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Tax Period <span class="text-danger">*</span></label>
                            <input type="text" name="tax_period" class="form-control" id="editTaxPeriod" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Amount (BDT) <span class="text-danger">*</span></label>
                            <div class="input-group tp-input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control font-monospace" id="editAmount" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" id="editPaymentMethod" required>
                                <option value="treasury_challan">Treasury Challan (TR-6)</option>
                                <option value="e_payment">e-Payment / iVAS Portal</option>
                                <option value="bank_transfer">Bank Transfer / Pay Order</option>
                                <option value="online_portal">NBR Online Portal</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Treasury Challan Number</label>
                            <input type="text" name="challan_number" class="form-control font-monospace" id="editChallanNumber">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Mushak / NBR Reference</label>
                            <input type="text" name="mushak_reference" class="form-control font-monospace" id="editMushakReference">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" id="editPaymentStatus" required>
                                <option value="completed">Completed (Verified)</option>
                                <option value="pending">Pending Verification</option>
                                <option value="rejected">Rejected / Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label tp-form-label">Replace Challan Document (Optional)</label>
                            <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <div id="editCurrentFileBox" class="mt-2 small"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label tp-form-label">Administrative Notes</label>
                            <textarea name="notes" class="form-control" id="editNotes" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer tp-modal-footer">
                    <button type="button" class="btn btn-light border tp-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary tp-btn">
                        <i class="fas fa-check-circle me-1"></i>
                        Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- View Payment Details Modal --}}
<div class="modal fade tp-modal" id="viewPaymentModal" tabindex="-1" aria-labelledby="viewPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tp-modal-content">
            <div class="modal-header tp-modal-header">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="viewPaymentModalLabel">
                    <span class="tp-modal-icon tp-icon-info">
                        <i class="fas fa-file-invoice"></i>
                    </span>
                    Treasury Payment Details
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body tp-modal-body" id="viewPaymentModalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fs-4 text-primary"></i>
                </div>
            </div>

            <div class="modal-footer tp-modal-footer">
                <button type="button" class="btn btn-light border tp-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


{{-- Global Hidden Delete Form --}}
<form id="globalDeletePaymentForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>


<style>
    /* =========================================================
       TAX PAYMENTS - KAIADMIN WHITE CLASSIC UI
       Page-scoped styles
    ========================================================= */

    .tax-payments-wrapper {
        --tp-primary: var(--bs-primary, #1572e8);
        --tp-success: var(--bs-success, #31ce36);
        --tp-warning: var(--bs-warning, #ffad46);
        --tp-danger: var(--bs-danger, #f25961);
        --tp-info: var(--bs-info, #48abf7);
        --tp-body-bg: #f8f9fa;
        --tp-card-bg: #ffffff;
        --tp-heading: #2a2f5b;
        --tp-text: #495057;
        --tp-muted: #8d9498;
        --tp-border: #ebecec;
        --tp-radius: 5px;
        color: var(--tp-text);
        min-width: 0;
    }

    /* Page Header */
    .tax-payments-wrapper .tp-page-header {
        min-width: 0;
    }

    .tax-payments-wrapper .tp-page-heading {
        min-width: 0;
    }

    .tax-payments-wrapper .tp-page-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--tp-heading);
        font-size: clamp(1.1rem, 1.7vw, 1.4rem);
        font-weight: 700;
        line-height: 1.35;
    }

    .tax-payments-wrapper .tp-title-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--tp-success);
        background: rgba(49, 206, 54, .10);
        border-radius: var(--tp-radius);
        font-size: 16px;
    }

    .tax-payments-wrapper .tp-page-description {
        color: var(--tp-muted);
        font-size: 13px;
        line-height: 1.6;
        padding-left: 46px;
    }

    .tax-payments-wrapper .tp-record-badge {
        color: var(--tp-success);
        background: rgba(49, 206, 54, .10);
        border: 1px solid rgba(49, 206, 54, .18);
        border-radius: var(--tp-radius);
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 600;
    }

    .tax-payments-wrapper .tp-header-actions {
        flex-shrink: 0;
    }

    /* Cards */
    .tax-payments-wrapper .tp-card {
        background: var(--tp-card-bg);
        border: 1px solid var(--tp-border);
        border-radius: var(--tp-radius);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        min-width: 0;
    }

    .tax-payments-wrapper .tp-metric-card .card-body {
        padding: 18px;
    }

    .tax-payments-wrapper .tp-metric-content {
        min-width: 0;
    }

    .tax-payments-wrapper .tp-metric-label {
        display: block;
        color: var(--tp-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .45px;
        line-height: 1.5;
        text-transform: uppercase;
    }

    .tax-payments-wrapper .tp-metric-value {
        margin-top: 7px;
        font-family: var(--bs-font-monospace);
        font-size: clamp(1.15rem, 1.8vw, 1.5rem);
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .tax-payments-wrapper .tp-metric-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: var(--tp-radius);
        font-size: 19px;
    }

    .tax-payments-wrapper .tp-icon-success {
        color: var(--tp-success);
        background: rgba(49, 206, 54, .11);
    }

    .tax-payments-wrapper .tp-icon-warning {
        color: #d99020;
        background: rgba(255, 173, 70, .15);
    }

    .tax-payments-wrapper .tp-icon-primary {
        color: var(--tp-primary);
        background: rgba(21, 114, 232, .10);
    }

    .tax-payments-wrapper .tp-icon-info {
        color: var(--tp-info);
        background: rgba(72, 171, 247, .12);
    }

    /* Section Headers */
    .tax-payments-wrapper .tp-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 15px 18px;
        background: #fff;
        border-bottom: 1px solid var(--tp-border);
        border-radius: var(--tp-radius) var(--tp-radius) 0 0;
    }

    .tax-payments-wrapper .tp-section-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--tp-primary);
        background: rgba(21, 114, 232, .09);
        border-radius: var(--tp-radius);
        font-size: 13px;
    }

    .tax-payments-wrapper .tp-section-title {
        color: var(--tp-heading);
        font-size: 14px;
        font-weight: 700;
    }

    /* Forms */
    .tax-payments-wrapper .tp-filter-card .card-body {
        padding: 18px;
    }

    .tax-payments-wrapper .tp-form-label {
        display: block;
        margin-bottom: 7px;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
    }

    .tax-payments-wrapper .form-control,
    .tax-payments-wrapper .form-select,
    .tp-modal .form-control,
    .tp-modal .form-select {
        min-height: 38px;
        border: 1px solid #e4e6e9;
        border-radius: var(--tp-radius);
        color: #495057;
        background-color: #fff;
        font-size: 13px;
        box-shadow: none;
    }

    .tax-payments-wrapper textarea.form-control,
    .tp-modal textarea.form-control {
        min-height: auto;
        line-height: 1.6;
        resize: vertical;
    }

    .tax-payments-wrapper .form-control::placeholder,
    .tp-modal .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    .tax-payments-wrapper .form-control:focus,
    .tax-payments-wrapper .form-select:focus,
    .tp-modal .form-control:focus,
    .tp-modal .form-select:focus {
        border-color: rgba(21, 114, 232, .55);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08);
    }

    .tax-payments-wrapper .tp-input-group .input-group-text,
    .tp-modal .tp-input-group .input-group-text {
        min-height: 38px;
        padding: 0 12px;
        color: #6c757d;
        background: #f8f9fa;
        border: 1px solid #e4e6e9;
        border-radius: var(--tp-radius);
        font-size: 13px;
    }

    .tax-payments-wrapper .tp-input-group .form-control,
    .tp-modal .tp-input-group .form-control {
        min-width: 0;
    }

    .tax-payments-wrapper .tp-input-group .input-group-text:first-child,
    .tp-modal .tp-input-group .input-group-text:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .tax-payments-wrapper .tp-input-group .input-group-text + .form-control,
    .tp-modal .tp-input-group .input-group-text + .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    /* Buttons */
    .tax-payments-wrapper .tp-btn,
    .tp-modal .tp-btn {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 13px;
        border-radius: var(--tp-radius);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        white-space: nowrap;
        transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .tax-payments-wrapper .tp-btn:focus-visible,
    .tp-modal .tp-btn:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .15);
    }

    .tax-payments-wrapper .tp-reset-btn {
        min-width: 38px;
        padding-left: 10px;
        padding-right: 10px;
    }

    /* Table */
    .tax-payments-wrapper .tp-table-card {
        overflow: hidden;
    }

    .tax-payments-wrapper .tp-table-responsive {
        width: 100%;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
    }

    .tax-payments-wrapper .tp-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0;
        color: #495057;
        font-size: 12px;
    }

    .tax-payments-wrapper .tp-table thead th {
        padding: 12px 10px;
        color: #6c757d;
        background: #f8f9fa;
        border-bottom: 1px solid var(--tp-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .tax-payments-wrapper .tp-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #f0f1f3;
        vertical-align: middle;
    }

    .tax-payments-wrapper .tp-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .tax-payments-wrapper .tp-table tbody tr:hover {
        background: #fafbfd;
    }

    .tax-payments-wrapper .tp-col-number {
        width: 45px;
    }

    .tax-payments-wrapper .tp-col-actions {
        width: 112px;
    }

    .tax-payments-wrapper .tp-table-subtext {
        display: block;
        margin-top: 3px;
        color: var(--tp-muted);
        font-size: 10.5px;
        line-height: 1.4;
    }

    .tax-payments-wrapper .tp-period-badge {
        padding: 5px 8px;
        color: #686868;
        background: #f1f2f4;
        border: 1px solid #e8e9eb;
        border-radius: var(--tp-radius);
        font-family: var(--bs-font-monospace);
        font-size: 10.5px;
        font-weight: 600;
    }

    .tax-payments-wrapper .tp-reference {
        font-size: 11.5px;
        white-space: nowrap;
    }

    .tax-payments-wrapper .tp-reference-badge {
        max-width: 180px;
        overflow-wrap: anywhere;
        color: #6c757d;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: var(--tp-radius);
        font-family: var(--bs-font-monospace);
        font-size: 10px;
        font-weight: 500;
    }

    .tax-payments-wrapper .tp-notes-text {
        max-width: 170px;
        margin-top: 3px;
        font-size: 10.5px;
    }

    .tax-payments-wrapper .tp-amount {
        font-size: 13px;
        white-space: nowrap;
    }

    .tax-payments-wrapper .tp-document-btn {
        padding: 5px 8px;
        border-radius: var(--tp-radius);
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .tax-payments-wrapper .tp-no-file-badge {
        padding: 5px 8px;
        color: #858b91;
        background: #f5f6f7;
        border: 1px solid #e9ecef;
        border-radius: var(--tp-radius);
        font-size: 10px;
        font-weight: 600;
    }

    .tax-payments-wrapper .tp-status-badge {
        padding: 5px 8px;
        border: 1px solid transparent;
        border-radius: var(--tp-radius);
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .tax-payments-wrapper .tp-status-completed {
        color: #20a44b;
        background: rgba(49, 206, 54, .10);
        border-color: rgba(49, 206, 54, .18);
    }

    .tax-payments-wrapper .tp-status-pending {
        color: #bd811c;
        background: rgba(255, 173, 70, .14);
        border-color: rgba(255, 173, 70, .22);
    }

    .tax-payments-wrapper .tp-status-rejected {
        color: #dc3545;
        background: rgba(242, 89, 97, .09);
        border-color: rgba(242, 89, 97, .18);
    }

    .tax-payments-wrapper .tp-action-group {
        gap: 4px;
    }

    .tax-payments-wrapper .tp-action-group .btn {
        width: 29px;
        height: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid #e7e9ed;
        border-radius: var(--tp-radius) !important;
        font-size: 11px;
    }

    .tax-payments-wrapper .tp-action-group .btn:hover {
        box-shadow: 0 2px 5px rgba(0, 0, 0, .06);
    }

    .tax-payments-wrapper .tp-table-count {
        color: #7b838a;
        background: #f7f8fa;
        border: 1px solid #eceef0;
        border-radius: var(--tp-radius);
        padding: 6px 10px;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Empty State */
    .tax-payments-wrapper .tp-empty-state {
        max-width: 380px;
        margin: 0 auto;
        padding: 12px;
    }

    .tax-payments-wrapper .tp-empty-icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        color: #9aa1a8;
        background: #f3f5f7;
        border-radius: 50%;
        font-size: 23px;
    }

    /* Pagination */
    .tax-payments-wrapper .tp-pagination-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 13px 18px;
        background: #fff;
        border-top: 1px solid var(--tp-border);
    }

    .tax-payments-wrapper .tp-pagination-footer nav {
        max-width: 100%;
        overflow-x: auto;
    }

    .tax-payments-wrapper .tp-pagination-footer .pagination {
        margin-bottom: 0;
        gap: 4px;
        flex-wrap: wrap;
    }

    .tax-payments-wrapper .tp-pagination-footer .page-link {
        border-radius: var(--tp-radius);
        font-size: 12px;
    }

    /* Alerts */
    .tax-payments-wrapper .tp-alert {
        position: relative;
        padding: 12px 42px 12px 14px;
        border-radius: var(--tp-radius);
        font-size: 12px;
    }

    /* Modal */
    .tp-modal .modal-dialog {
        width: auto;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    .tp-modal .modal-content {
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--tp-border);
        border-radius: 6px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .12);
    }

    .tp-modal .tp-modal-header {
        padding: 16px 20px;
        background: #fff;
        border-bottom: 1px solid var(--tp-border);
    }

    .tp-modal .tp-modal-header .modal-title {
        color: #2a2f5b;
        font-size: 14px;
    }

    .tp-modal .tp-modal-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--tp-radius);
        font-size: 13px;
    }

    .tp-modal .tp-modal-body {
        padding: 20px;
    }

    .tp-modal .tp-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 14px 20px;
        background: #fff;
        border-top: 1px solid var(--tp-border);
    }

    .tp-modal .tp-help-text {
        display: block;
        margin-top: 7px;
        color: #8d9498;
        font-size: 11px;
        line-height: 1.5;
    }

    .tp-modal .list-group-item {
        border-color: #eff0f2;
        font-size: 12px;
    }

    .tp-modal .list-group-item > span:last-child {
        max-width: 65%;
        text-align: right;
        overflow-wrap: anywhere;
    }

    /* Modal dynamic content compatibility */
    #editCurrentFileBox .badge,
    #viewPaymentModalBody .badge {
        border-radius: var(--tp-radius);
        font-size: 10.5px;
    }

    #viewPaymentModalBody .btn {
        border-radius: var(--tp-radius);
    }

    #viewPaymentModalBody .list-group-item {
        gap: 12px;
    }

    #viewPaymentModalBody .list-group-item > span:first-child {
        flex-shrink: 0;
    }

    #viewPaymentModalBody .list-group-item > span:last-child {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       RESPONSIVE BREAKPOINTS
    ========================================================= */

    @media (max-width: 1199.98px) {
        .tax-payments-wrapper .tp-filter-card .card-body {
            padding: 15px;
        }
    }

    @media (max-width: 767.98px) {
        .tax-payments-wrapper .tp-page-description {
            padding-left: 0;
        }

        .tax-payments-wrapper .tp-metric-card .card-body {
            padding: 15px;
        }

        .tax-payments-wrapper .tp-card-header {
            padding: 13px 14px;
        }

        .tax-payments-wrapper .tp-filter-card .card-body {
            padding: 14px;
        }

        .tax-payments-wrapper .tp-pagination-footer {
            justify-content: center;
            padding: 12px;
        }

        .tp-modal .modal-dialog {
            margin: 12px;
        }

        .tp-modal .tp-modal-header {
            padding: 14px;
        }

        .tp-modal .tp-modal-body {
            padding: 15px;
        }

        .tp-modal .tp-modal-footer {
            padding: 12px 15px;
        }
    }

    @media (max-width: 575.98px) {
        .tax-payments-wrapper {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-payments-wrapper .tp-page-header {
            gap: 12px !important;
        }

        .tax-payments-wrapper .tp-page-title {
            font-size: 18px;
        }

        .tax-payments-wrapper .tp-title-icon {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .tax-payments-wrapper .tp-page-description {
            font-size: 11.5px;
            line-height: 1.5;
        }

        .tax-payments-wrapper .tp-header-actions {
            width: 100%;
        }

        .tax-payments-wrapper .tp-header-actions .tp-btn {
            flex: 1;
        }

        .tax-payments-wrapper .tp-metric-value {
            font-size: 1.15rem;
        }

        .tax-payments-wrapper .tp-metric-icon {
            width: 40px;
            height: 40px;
            font-size: 17px;
        }

        .tax-payments-wrapper .tp-filter-card .card-body {
            padding: 12px;
        }

        .tax-payments-wrapper .tp-table {
            min-width: 1120px;
        }

        .tax-payments-wrapper .tp-table thead th,
        .tax-payments-wrapper .tp-table tbody td {
            padding: 10px 8px;
        }

        .tax-payments-wrapper .tp-alert {
            padding-left: 11px;
        }

        .tp-modal .modal-dialog {
            margin: 8px;
        }

        .tp-modal .modal-content {
            max-height: calc(100dvh - 16px);
        }

        .tp-modal .modal-body {
            overflow-y: auto;
        }

        .tp-modal .tp-modal-header {
            padding: 12px;
        }

        .tp-modal .tp-modal-body {
            padding: 12px;
        }

        .tp-modal .tp-modal-footer {
            padding: 10px 12px;
        }

        .tp-modal .tp-modal-footer .tp-btn {
            flex: 1;
        }

        #viewPaymentModalBody .list-group-item.d-flex {
            align-items: flex-start;
            gap: 10px;
        }

        #viewPaymentModalBody .list-group-item.d-flex > :last-child {
            text-align: right;
            overflow-wrap: anywhere;
        }
    }

    @media (max-width: 359.98px) {
        .tax-payments-wrapper .tp-page-title {
            font-size: 16px;
        }

        .tax-payments-wrapper .tp-record-badge {
            font-size: 10px;
            padding: 4px 6px;
        }

        .tax-payments-wrapper .tp-header-actions {
            gap: 6px !important;
        }

        .tax-payments-wrapper .tp-header-actions .tp-btn {
            padding-left: 8px;
            padding-right: 8px;
            font-size: 11px;
        }

        .tp-modal .tp-modal-header .modal-title {
            font-size: 12px;
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