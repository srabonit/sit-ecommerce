@extends('layouts.admin')

@section('title', 'Tax Rules')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 tax-rules-wrapper">
    <!-- Header & Action Controls -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2 text-nowrap">
                <i class="fas fa-scale-balanced text-primary"></i> Tax &amp; VAT Rules
            </h4>
            <span class="text-secondary small d-none d-md-inline">•</span>
            <span class="text-secondary small mb-0 text-nowrap">Manage calculation rules, rates &amp; branch overrides</span>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end page-actions">
            <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.tax.rules.create') }}" class="btn btn-sm btn-primary px-3 flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="fas fa-plus-circle"></i> <span>Add Tax Rule</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
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

    <!-- Priority Instruction Guide -->
    <div class="card border border-info border-opacity-25 bg-info bg-opacity-10 shadow-sm mb-3">
        <div class="card-body p-2 p-md-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-info text-info fs-5 flex-shrink-0"></i>
                <div class="small text-dark" style="font-size: 11.5px; line-height: 1.4;">
                    <strong>অগ্রাধিকার নিয়ম (Priority System):</strong> যে রুলের <strong>Priority</strong> নম্বর বেশি (যেমন: 100 > 10), সিস্টেম সেই রুলটিকে সবার আগে কার্যকর করবে। নির্দিষ্ট ব্রাঞ্চে ০% ভ্যাট কার্যকর করতে ওই ব্রাঞ্চের রুলের প্রায়োরিটি গ্লোবাল রুলের চেয়ে বেশি রাখুন।
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Filter Panel -->
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="card-body p-2 p-md-3">
            <form action="{{ route('admin.tax.rules.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search rule or code..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Tax Class Filter -->
                <div class="col-6 col-md-2">
                    <select name="tax_class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        @foreach($taxClasses as $tc)
                            <option value="{{ $tc->id }}" {{ request('tax_class_id') == $tc->id ? 'selected' : '' }}>
                                {{ $tc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Calculation Type (Inclusive vs Exclusive) -->
                <div class="col-6 col-md-2">
                    <select name="calculation_type" class="form-select form-select-sm">
                        <option value="">All Modes</option>
                        <option value="exclusive" {{ request('calculation_type') === 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                        <option value="inclusive" {{ request('calculation_type') === 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                    </select>
                </div>

                <!-- Status Type (Taxable, Exempt, Zero-Rated) -->
                <div class="col-6 col-md-2">
                    <select name="status_type" class="form-select form-select-sm">
                        <option value="">All Tax Types</option>
                        <option value="taxable" {{ request('status_type') === 'taxable' ? 'selected' : '' }}>Taxable</option>
                        <option value="exempt" {{ request('status_type') === 'exempt' ? 'selected' : '' }}>Exempt (0%)</option>
                        <option value="zero_rated" {{ request('status_type') === 'zero_rated' ? 'selected' : '' }}>Zero-Rated (0%)</option>
                    </select>
                </div>

                <!-- Active State -->
                <div class="col-6 col-md-1">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-12 col-md-2 d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="fas fa-filter"></i> <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'tax_class_id', 'calculation_type', 'status_type', 'status']))
                        <a href="{{ route('admin.tax.rules.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Tax Rules Table Card -->
    <div class="card border border-light-subtle shadow-sm bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 12.5px;">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-3 text-center py-2" style="width: 60px;">Priority</th>
                        <th class="py-2">Rule Details</th>
                        <th class="py-2">Tax Class</th>
                        <th class="text-end py-2">Applied Rate</th>
                        <th class="text-center py-2">Mode</th>
                        <th class="text-center py-2">Tax Type</th>
                        <th class="text-center py-2">Conditions</th>
                        <th class="py-2">Validity</th>
                        <th class="text-center py-2">Status</th>
                        <th class="text-end pe-3 py-2" style="width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxRules as $rule)
                        <tr>
                            <!-- Priority Badge -->
                            <td class="ps-3 text-center">
                                <span class="badge bg-light text-dark border px-2 py-1 font-monospace" title="Priority: {{ $rule->priority }}">
                                    #{{ $rule->priority }}
                                </span>
                            </td>

                            <!-- Rule Info -->
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 200px;" title="{{ $rule->name }}">{{ $rule->name }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-secondary-subtle text-secondary px-1 font-monospace" style="font-size: 10px;">
                                        {{ $rule->code }}
                                    </span>
                                    @if($rule->admin_note)
                                        <span class="text-secondary small text-truncate" style="max-width: 140px; font-size: 11px;" title="{{ $rule->admin_note }}">
                                            {{ $rule->admin_note }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tax Class -->
                            <td>
                                @if($rule->taxClass)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
                                        {{ $rule->taxClass->name }}
                                    </span>
                                @else
                                    <span class="text-secondary small">General</span>
                                @endif
                            </td>

                            <!-- Rate or Fixed Amount -->
                            <td class="text-end">
                                @if($rule->status_type === 'exempt')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 font-monospace">Exempt (0%)</span>
                                @elseif($rule->status_type === 'zero_rated')
                                    <span class="badge bg-info-subtle text-info px-2 py-1 font-monospace">Zero-Rated (0%)</span>
                                @else
                                    @if($rule->tax_type === 'percentage')
                                        <span class="fw-bold text-dark fs-6 font-monospace">{{ number_format($rule->rate, 2) }}%</span>
                                    @else
                                        <span class="fw-bold text-dark fs-6 font-monospace">৳{{ number_format($rule->fixed_amount, 2) }}</span>
                                        <small class="text-secondary d-block" style="font-size: 10px;">Fixed Amount</small>
                                    @endif
                                @endif
                            </td>

                            <!-- Calculation Mode -->
                            <td class="text-center">
                                @if($rule->calculation_type === 'inclusive')
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1" title="Tax included in product price">
                                        Inclusive
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" title="Tax added to product price">
                                        Exclusive
                                    </span>
                                @endif
                            </td>

                            <!-- Status Type -->
                            <td class="text-center">
                                @if($rule->status_type === 'taxable')
                                    <span class="badge bg-success-subtle text-success px-2 py-1">Taxable</span>
                                @elseif($rule->status_type === 'exempt')
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1">Exempt</span>
                                @else
                                    <span class="badge bg-info-subtle text-info px-2 py-1">Zero-Rated</span>
                                @endif
                            </td>

                            <!-- Linked Conditions -->
                            <td class="text-center">
                                @if($rule->conditions_count > 0)
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                        <i class="fas fa-diagram-project text-primary me-1"></i> {{ $rule->conditions_count }} Rules
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info px-2 py-1">All Items</span>
                                @endif
                            </td>

                            <!-- Validity Period -->
                            <td>
                                @if($rule->valid_from || $rule->valid_to)
                                    <span class="d-block text-secondary small font-monospace" style="font-size: 11px;">
                                        {{ $rule->valid_from ? \Carbon\Carbon::parse($rule->valid_from)->format('d M y') : 'Start' }}
                                        -
                                        {{ $rule->valid_to ? \Carbon\Carbon::parse($rule->valid_to)->format('d M y') : 'Open' }}
                                    </span>
                                @else
                                    <span class="text-success small fw-medium" style="font-size: 11px;">Always Active</span>
                                @endif
                            </td>

                            <!-- Active Status Toggle Switch -->
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input rule-status-toggle" type="checkbox" role="switch"
                                        data-id="{{ $rule->id }}" {{ $rule->is_active ? 'checked' : '' }}
                                        title="Toggle active status" style="cursor: pointer;">
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.tax.rules.edit', $rule->id) }}" class="btn btn-outline-primary border-light-subtle py-1 px-2" title="Edit Rule">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger border-light-subtle py-1 px-2 delete-rule-btn"
                                        data-id="{{ $rule->id }}"
                                        data-name="{{ $rule->name }}"
                                        title="Delete Rule">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-secondary">
                                    <i class="fas fa-scale-balanced fs-2 opacity-50 mb-2 d-block"></i>
                                    <h6 class="fw-semibold text-dark">No Tax Rules Found</h6>
                                    <p class="small mb-3">Create specific tax rules for your items, categories, or branches.</p>
                                    <a href="{{ route('admin.tax.rules.create') }}" class="btn btn-sm btn-primary px-3">
                                        <i class="fas fa-plus-circle me-1"></i> Create Tax Rule
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($taxRules->hasPages())
            <div class="card-footer bg-white border-0 py-2 px-3">
                <div class="d-flex justify-content-center justify-content-md-end">
                    {{ $taxRules->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modern UI Delete Confirmation Modal -->
<div class="modal fade" id="deleteRuleConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-trash-alt text-danger"></i> Delete Rule
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <div class="p-2 mb-2 rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <div class="fw-semibold text-dark small mb-1">
                    Delete <span class="text-danger fw-bold" id="deleteRuleTargetName"></span>?
                </div>
                <span class="text-secondary d-block" style="font-size: 11px;">
                    All associated conditions will also be removed.
                </span>
            </div>
            <div class="modal-footer border-0 pt-0 px-3 pb-3 d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-sm btn-light border px-3 flex-fill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger px-3 fw-semibold flex-fill" id="confirmDeleteRuleBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="globalDeleteRuleForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
    /* Strict 5px Corner Radius */
    .tax-rules-wrapper .card,
    .tax-rules-wrapper .btn,
    .tax-rules-wrapper .form-control,
    .tax-rules-wrapper .form-select,
    .tax-rules-wrapper .input-group-text,
    .tax-rules-wrapper .badge,
    .tax-rules-wrapper .alert,
    .tax-rules-wrapper .btn-group,
    #deleteRuleConfirmModal .modal-content,
    #deleteRuleConfirmModal .btn {
        border-radius: 5px !important;
    }

    .tax-rules-wrapper .btn-group .btn:first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
    }

    .tax-rules-wrapper .btn-group .btn:last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
    }

    /* Switch Styling */
    .tax-rules-wrapper .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    /* Mobile Protection */
    @media (max-width: 767.98px) {
        .tax-rules-wrapper {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .tax-rules-wrapper .card-footer {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        #deleteRuleConfirmModal .modal-dialog {
            margin: 0.5rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Status toggle AJAX
        document.querySelectorAll('.rule-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const id = this.dataset.id;
                const originalState = !this.checked;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch(`{{ url('admin/tax/rules/toggle-status') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        this.checked = originalState;
                        alert(data.message || 'Status update failed.');
                    }
                })
                .catch(() => {
                    this.checked = originalState;
                    alert('Server error: status update failed.');
                });
            });
        });

        // Modern Delete Confirmation Modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteRuleConfirmModal'));
        const deleteTargetName = document.getElementById('deleteRuleTargetName');
        const confirmBtn = document.getElementById('confirmDeleteRuleBtn');
        let pendingRuleId = null;

        document.querySelectorAll('.delete-rule-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                pendingRuleId = this.dataset.id;
                deleteTargetName.textContent = `"${this.dataset.name}"`;
                deleteModal.show();
            });
        });

        confirmBtn.addEventListener('click', function () {
            if (pendingRuleId) {
                const form = document.getElementById('globalDeleteRuleForm');
                form.action = `{{ url('admin/tax/rules/delete') }}/${pendingRuleId}`;
                form.submit();
            }
        });
    });
</script>
@endsection