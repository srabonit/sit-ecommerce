@extends('layouts.admin')

@section('title', 'Tax Classes')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-3 tax-classes-page">
   
    <div class="tax-page-header d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-4">
    <!-- Left: Title & Subtitle Wrapper -->
    <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #e0f2fe; color: #0284c7; border-radius: 8px; font-size: 16px;">
            <i class="fas fa-shapes"></i>
        </span>
        <div class="min-w-0 lh-sm">
            <h4 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 16px;">
                Tax Classes
            </h4>
            <div class="text-secondary text-truncate m-0 p-0" style="font-size: 11.5px;">
                Manage categories &amp; rates
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex align-items-center gap-2 flex-wrap flex-lg-nowrap justify-content-start justify-content-lg-end page-actions w-100 w-lg-auto">
        <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1 text-nowrap flex-shrink-0" style="height: 34px;">
            <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
        </a>
        <button type="button" class="btn btn-sm btn-primary px-3 d-inline-flex align-items-center justify-content-center gap-1 text-nowrap flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createTaxClassModal" style="height: 34px;">
            <i class="fas fa-plus-circle"></i> <span>Add Tax Class</span>
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
                <span class="fw-semibold small">Please resolve errors:</span>
            </div>
            <ul class="mb-0 ps-3 small text-secondary">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="card tax-filter-card border-0 shadow-sm bg-white mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.tax.classes.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-12 col-md-5">
                    <div class="input-group tax-search-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search class or code..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm tax-filter-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-6 col-md-4 d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="fas fa-filter"></i> <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.tax.classes.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters" aria-label="Reset Filters">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tax Classes Table Card -->
    <div class="card tax-table-card border-0 shadow-sm bg-white mb-4">
        <div class="table-responsive tax-table-responsive">
            <table class="table table-hover align-middle mb-0 tax-classes-table">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-3 py-2" style="width: 45px;">#</th>
                        <th class="py-2">Class &amp; Code</th>
                        <th class="py-2">Description</th>
                        <th class="text-center py-2">Rules</th>
                        <th class="text-center py-2">Products</th>
                        <th class="text-center py-2">Status</th>
                        <th class="py-2">Created By</th>
                        <th class="text-end pe-3 py-2" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxClasses as $index => $class)
                        <tr>
                            <td class="ps-3 font-monospace text-secondary">{{ $taxClasses->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $class->name }}</div>
                                <span class="badge bg-light text-secondary border font-monospace mt-1 px-1" style="font-size: 10.5px;">
                                    {{ $class->code }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate text-secondary" style="max-width: 260px;" title="{{ $class->description }}">
                                    {{ $class->description ?: 'No description' }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($class->tax_rules_count > 0)
                                    <a href="{{ route('admin.tax.rules.index', ['tax_class_id' => $class->id]) }}" class="badge bg-info-subtle text-info border border-info-subtle text-decoration-none px-2 py-1">
                                        <i class="fas fa-scale-balanced me-1"></i> {{ $class->tax_rules_count }} Rules
                                    </a>
                                @else
                                    <span class="badge bg-light text-secondary border px-2 py-1">0 Rules</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($class->products_count > 0)
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                        <i class="fas fa-box me-1"></i> {{ $class->products_count }} Products
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border px-2 py-1">0 Products</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block m-0">
                                    <input class="form-check-input status-toggle-switch" type="checkbox" role="switch"
                                        data-id="{{ $class->id }}" {{ $class->is_active ? 'checked' : '' }}
                                        title="Toggle status" style="cursor: pointer;">
                                </div>
                            </td>
                            <td>
                                <span class="text-dark d-block fw-medium small">{{ $class->creator ? $class->creator->name : 'System' }}</span>
                                <span class="text-secondary" style="font-size: 10.5px;">{{ $class->created_at ? $class->created_at->format('d M Y') : '-' }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary border-light-subtle py-1 px-2 edit-tax-class-btn"
                                        data-id="{{ $class->id }}"
                                        data-name="{{ $class->name }}"
                                        data-code="{{ $class->code }}"
                                        data-description="{{ $class->description }}"
                                        data-is-active="{{ $class->is_active ? '1' : '0' }}"
                                        title="Edit Class" aria-label="Edit Class">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger border-light-subtle py-1 px-2 delete-tax-class-btn"
                                        data-id="{{ $class->id }}"
                                        data-name="{{ $class->name }}"
                                        data-rules="{{ $class->tax_rules_count }}"
                                        data-products="{{ $class->products_count }}"
                                        title="Delete Class" aria-label="Delete Class">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-secondary">
                                    <i class="fas fa-shapes fs-2 opacity-50 mb-2 d-block"></i>
                                    <h6 class="fw-semibold text-dark">No Tax Classes Found</h6>
                                    <p class="small mb-3">Create a tax class to organize your rates.</p>
                                    <button type="button" class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#createTaxClassModal">
                                        <i class="fas fa-plus-circle me-1"></i> Add Tax Class
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($taxClasses->hasPages())
            <div class="card-footer bg-white border-0 py-2 px-3 d-flex justify-content-center justify-content-md-end">
                {{ $taxClasses->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Create Tax Class -->
<div class="modal fade" id="createTaxClassModal" tabindex="-1" aria-labelledby="createTaxClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tax-modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="createTaxClassModalLabel">
                    <i class="fas fa-plus-circle text-primary"></i> Add Tax Class
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tax.classes.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" id="createClassNameInput" placeholder="Standard VAT, Reduced Rate" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">Class Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-sm font-monospace text-uppercase" id="createClassCodeInput" placeholder="STANDARD_VAT" required>
                        <span class="text-secondary d-block mt-1" style="font-size: 11px;">Unique code (auto-uppercased).</span>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Short description or notes..."></textarea>
                    </div>

                    <div class="form-check form-switch p-2 border rounded-1 bg-light mt-2">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                        <label class="form-check-label fw-semibold text-dark small" for="createIsActive">Active Status</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="fas fa-save"></i> <span>Save Class</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Tax Class -->
<div class="modal fade" id="editTaxClassModal" tabindex="-1" aria-labelledby="editTaxClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tax-modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="editTaxClassModalLabel">
                    <i class="fas fa-edit text-primary"></i> Edit Tax Class
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTaxClassForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" id="editClassName" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">Class Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-sm font-monospace text-uppercase" id="editClassCode" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">Description</label>
                        <textarea name="description" class="form-control form-control-sm" id="editClassDescription" rows="3"></textarea>
                    </div>

                    <div class="form-check form-switch p-2 border rounded-1 bg-light mt-2">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" value="1" id="editIsActive">
                        <label class="form-check-label fw-semibold text-dark small" for="editIsActive">Active Status</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="fas fa-check-circle"></i> <span>Update Class</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Custom UI-Friendly Delete & Warning Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content tax-modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="deleteModalTitle">
                    <i class="fas fa-trash-alt text-danger"></i> Delete Class
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <div class="p-2 mb-2 rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" id="deleteModalIcon">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <div class="fw-semibold text-dark small mb-1" id="deleteModalMsg">
                    Are you sure you want to delete <span class="text-danger fw-bold" id="deleteTargetName"></span>?
                </div>
                <span class="text-secondary d-block" style="font-size: 11px;" id="deleteModalSubtext">
                    This action cannot be undone.
                </span>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex justify-content-center gap-2" id="deleteModalFooter">
                <button type="button" class="btn btn-sm btn-light border px-3 flex-fill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger px-3 fw-semibold flex-fill" id="confirmDeleteActionBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Global Hidden Delete Form -->
<form id="globalDeleteTaxClassForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
    /* Tax Classes page — Kaiadmin Lite inspired White Classic UI */
    .tax-classes-page {
        --tax-primary: var(--bs-primary, #1572e8);
        --tax-border: #e6e9ef;
        --tax-muted: #6b7280;
        --tax-surface: #ffffff;
        --tax-page-bg: #f5f7fb;
        color: #2a2f3a;
        min-width: 0;
    }

    .tax-classes-page .card,
    .tax-classes-page .btn,
    .tax-classes-page .form-control,
    .tax-classes-page .form-select,
    .tax-classes-page .input-group-text,
    .tax-classes-page .badge,
    .tax-classes-page .alert,
    .tax-classes-page .rounded-1,
    .tax-modal-content,
    .tax-modal-content .btn,
    .tax-modal-content .form-control,
    .tax-modal-content .form-select,
    .tax-modal-content .input-group-text,
    .tax-modal-content .badge {
        border-radius: 5px !important;
    }

    .tax-classes-page .tax-page-header h4 {
        font-size: clamp(1.1rem, 1.5vw, 1.35rem);
        letter-spacing: -0.025em;
    }

    .tax-classes-page .card {
        border: 1px solid var(--tax-border) !important;
        background: var(--tax-surface);
        box-shadow: 0 2px 8px rgba(28, 39, 60, 0.045) !important;
        overflow: hidden;
    }

    .tax-classes-page .btn {
        min-height: 34px;
        font-weight: 600;
        letter-spacing: 0.005em;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .tax-classes-page .btn:focus-visible,
    .tax-classes-page .form-control:focus,
    .tax-classes-page .form-select:focus {
        box-shadow: 0 0 0 .2rem rgba(21, 114, 232, .12);
    }

    .tax-classes-page .form-control,
    .tax-classes-page .form-select {
        min-height: 38px;
        border-color: #dfe3eb;
        color: #344054;
        font-size: .875rem;
    }

    .tax-classes-page .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    .tax-classes-page .input-group-text {
        border-color: #dfe3eb;
    }

    .tax-classes-page .tax-filter-card .card-body {
        padding: 1rem;
    }

    .tax-classes-page .tax-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tax-classes-page .tax-classes-table {
        width: 100%;
        min-width: 920px;
        font-size: 12.5px;
        color: #475467;
        border-color: #edf0f4;
    }

    .tax-classes-page .tax-classes-table thead th {
        padding: .8rem .7rem;
        background: #f8f9fc;
        color: #667085;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--tax-border);
    }

    .tax-classes-page .tax-classes-table tbody td {
        padding: .75rem .7rem;
        vertical-align: middle;
        border-color: #edf0f4;
    }

    .tax-classes-page .tax-classes-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .tax-classes-page .tax-classes-table tbody tr:hover {
        --bs-table-accent-bg: #f8faff;
    }

    .tax-classes-page .tax-classes-table .badge {
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.35;
    }

    .tax-classes-page .tax-classes-table .btn-group .btn {
        min-height: 30px;
        padding: .25rem .5rem;
    }

    .tax-classes-page .tax-classes-table .form-check-input {
        width: 2.1em;
        height: 1.1em;
        margin-top: 0;
        cursor: pointer;
    }

    .tax-classes-page .btn-group .btn:first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
    }

    .tax-classes-page .btn-group .btn:last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
    }

    .tax-classes-page .pagination {
        gap: .25rem;
        margin-bottom: 0;
    }

    .tax-classes-page .page-link {
        border-radius: 5px !important;
        font-size: .8rem;
    }

    .tax-modal-content {
        border: 1px solid var(--tax-border) !important;
        overflow: hidden;
    }

    .tax-modal-content .modal-header {
        background: #fff;
        border-color: var(--tax-border) !important;
    }

    .tax-modal-content .modal-title {
        font-size: 1rem;
    }

    .tax-modal-content .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .tax-modal-content .form-label {
        font-size: .8rem;
        margin-bottom: .4rem;
    }

    .tax-modal-content .form-control {
        min-height: 40px;
    }

    .tax-modal-content textarea.form-control {
        min-height: 88px;
    }

    .tax-modal-content .form-check.form-switch {
        border-color: var(--tax-border) !important;
        background: #f8f9fc !important;
        padding: .7rem .85rem .7rem 2.85rem !important;
    }

    .tax-modal-content .form-check-input {
        cursor: pointer;
    }

    @media (min-width: 1200px) {
        .tax-classes-page .tax-classes-table {
            min-width: 0;
        }

        .tax-classes-page .tax-classes-table thead th,
        .tax-classes-page .tax-classes-table tbody td {
            padding-left: .55rem;
            padding-right: .55rem;
        }
    }

    @media (max-width: 767.98px) {
        .tax-classes-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .tax-classes-page .tax-page-header {
            align-items: stretch !important;
        }

        .tax-classes-page .page-actions {
            width: 100%;
        }

        .tax-classes-page .tax-filter-card .card-body {
            padding: .85rem;
        }

        .tax-classes-page .tax-classes-table {
            min-width: 920px;
        }

        .tax-classes-page .card-footer {
            padding: .75rem !important;
        }
    }

    @media (max-width: 575.98px) {
        #createTaxClassModal .modal-dialog,
        #editTaxClassModal .modal-dialog,
        #deleteConfirmModal .modal-dialog {
            margin: .5rem;
        }

        #createTaxClassModal .modal-body,
        #editTaxClassModal .modal-body,
        #deleteConfirmModal .modal-body {
            padding: 1rem !important;
        }

        #createTaxClassModal .modal-header,
        #editTaxClassModal .modal-header,
        #deleteConfirmModal .modal-header {
            padding: .85rem 1rem !important;
        }

        #createTaxClassModal .modal-footer,
        #editTaxClassModal .modal-footer,
        #deleteConfirmModal .modal-footer {
            padding: 0 1rem 1rem !important;
        }

        .tax-classes-page .page-actions .btn {
            flex: 1 1 0;
            white-space: nowrap;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Auto-generate class code from name
        const nameInput = document.getElementById('createClassNameInput');
        const codeInput = document.getElementById('createClassCodeInput');
        if (nameInput && codeInput) {
            nameInput.addEventListener('input', function () {
                if (!codeInput.dataset.touched) {
                    codeInput.value = this.value
                        .trim()
                        .toUpperCase()
                        .replace(/[^A-Z0-9]/g, '_')
                        .replace(/_+/g, '_');
                }
            });
            codeInput.addEventListener('input', function () {
                codeInput.dataset.touched = 'true';
            });
        }

        // 2. Populate edit modal
        const editModal = new bootstrap.Modal(document.getElementById('editTaxClassModal'));
        document.querySelectorAll('.edit-tax-class-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const code = this.dataset.code;
                const desc = this.dataset.description;
                const isActive = this.dataset.isActive === '1';

                document.getElementById('editClassName').value = name;
                document.getElementById('editClassCode').value = code;
                document.getElementById('editClassDescription').value = desc || '';
                document.getElementById('editIsActive').checked = isActive;

                const form = document.getElementById('editTaxClassForm');
                form.action = `{{ url('admin/tax/classes/update') }}/${id}`;

                editModal.show();
            });
        });

        // 3. Status toggle AJAX
        document.querySelectorAll('.status-toggle-switch').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const id = this.dataset.id;
                const originalState = !this.checked;

                fetch(`{{ url('admin/tax/classes/toggle-status') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        this.checked = originalState;
                        alert(data.message || 'Failed to update status.');
                    }
                })
                .catch(() => {
                    this.checked = originalState;
                    alert('Server error: failed to update status.');
                });
            });
        });

        // 4. Modern UI Delete & Warning Modal (Replaces browser confirm/alert)
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        const deleteModalTitle = document.getElementById('deleteModalTitle');
        const deleteModalIcon = document.getElementById('deleteModalIcon');
        const deleteModalMsg = document.getElementById('deleteModalMsg');
        const deleteModalSubtext = document.getElementById('deleteModalSubtext');
        const deleteModalFooter = document.getElementById('deleteModalFooter');
        const confirmDeleteBtn = document.getElementById('confirmDeleteActionBtn');

        let pendingDeleteId = null;

        document.querySelectorAll('.delete-tax-class-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const rulesCount = parseInt(this.dataset.rules || '0');
                const productsCount = parseInt(this.dataset.products || '0');

                // Warning: Has active rules
                if (rulesCount > 0) {
                    deleteModalTitle.innerHTML = '<i class="fas fa-triangle-exclamation text-warning"></i> Cannot Delete Class';
                    deleteModalIcon.className = 'p-2 mb-2 rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center';
                    deleteModalIcon.innerHTML = '<i class="fas fa-triangle-exclamation fs-5"></i>';
                    deleteModalMsg.innerHTML = `<strong>"${name}"</strong> has <span class="badge bg-danger-subtle text-danger">${rulesCount} active tax rule(s)</span>.`;
                    deleteModalSubtext.innerText = 'Please reassign or delete the associated rules before removing this class.';
                    deleteModalFooter.innerHTML = '<button type="button" class="btn btn-sm btn-primary px-4 fw-semibold" data-bs-dismiss="modal">Understood</button>';
                    deleteModal.show();
                    return;
                }

                // Warning: Has assigned products
                if (productsCount > 0) {
                    deleteModalTitle.innerHTML = '<i class="fas fa-triangle-exclamation text-warning"></i> Cannot Delete Class';
                    deleteModalIcon.className = 'p-2 mb-2 rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center';
                    deleteModalIcon.innerHTML = '<i class="fas fa-box text-warning fs-5"></i>';
                    deleteModalMsg.innerHTML = `<strong>"${name}"</strong> is assigned to <span class="badge bg-danger-subtle text-danger">${productsCount} product(s)</span>.`;
                    deleteModalSubtext.innerText = 'Please reassign those products to another tax class first.';
                    deleteModalFooter.innerHTML = '<button type="button" class="btn btn-sm btn-primary px-4 fw-semibold" data-bs-dismiss="modal">Understood</button>';
                    deleteModal.show();
                    return;
                }

                // Clean Delete Confirmation
                pendingDeleteId = id;
                deleteModalTitle.innerHTML = '<i class="fas fa-trash-alt text-danger"></i> Delete Tax Class';
                deleteModalIcon.className = 'p-2 mb-2 rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center';
                deleteModalIcon.innerHTML = '<i class="fas fa-trash-alt fs-5"></i>';
                deleteModalMsg.innerHTML = `Are you sure you want to delete <span class="text-danger fw-bold">"${name}"</span>?`;
                deleteModalSubtext.innerText = 'This action is irreversible and will delete the class record.';
                deleteModalFooter.innerHTML = `
                    <button type="button" class="btn btn-sm btn-light border px-3 flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-danger px-3 fw-semibold flex-fill" id="confirmDeleteActionBtn">Delete</button>
                `;

                document.getElementById('confirmDeleteActionBtn').addEventListener('click', function () {
                    const deleteForm = document.getElementById('globalDeleteTaxClassForm');
                    deleteForm.action = `{{ url('admin/tax/classes/delete') }}/${pendingDeleteId}`;
                    deleteForm.submit();
                });

                deleteModal.show();
            });
        });
    });
</script>
@endsection