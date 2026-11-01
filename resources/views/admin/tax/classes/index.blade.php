@extends('layouts.admin')

@section('title', 'Tax Classes')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 tax-classes-page">
    <!-- Header & Action Controls (Title & Description Inline) -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2 text-nowrap">
                <i class="fas fa-shapes text-primary"></i> Tax Classes
            </h4>
            <span class="text-secondary small d-none d-md-inline">•</span>
            <span class="text-secondary small mb-0 text-nowrap">Manage categories &amp; rates</span>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end page-actions">
            <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary px-3 flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#createTaxClassModal">
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
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="card-body p-2 p-md-3">
            <form action="{{ route('admin.tax.classes.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search class or code..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
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
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 12.5px;">
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
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="createTaxClassModalLabel">
                    <i class="fas fa-plus-circle text-primary"></i> Add Tax Class
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tax.classes.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" id="createClassNameInput" placeholder="e.g. Standard VAT, Reduced Rate" required>
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
                <div class="modal-footer border-0 pt-0 px-3 pb-3">
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
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="editTaxClassModalLabel">
                    <i class="fas fa-edit text-primary"></i> Edit Tax Class
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTaxClassForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-3">
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
                <div class="modal-footer border-0 pt-0 px-3 pb-3">
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
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
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
            <div class="modal-footer border-0 pt-0 px-3 pb-3 d-flex justify-content-center gap-2" id="deleteModalFooter">
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
    /* Strict 5px Radius Rule */
    .tax-classes-page .card,
    .tax-classes-page .btn,
    .tax-classes-page .form-control,
    .tax-classes-page .form-select,
    .tax-classes-page .input-group-text,
    .tax-classes-page .badge,
    .tax-classes-page .alert,
    .tax-classes-page .rounded-1,
    .modal-content,
    .modal-content .btn,
    .modal-content .form-control,
    .modal-content .form-select,
    .modal-content .input-group-text,
    .modal-content .badge {
        border-radius: 5px !important;
    }

    /* Button Group Radius */
    .tax-classes-page .btn-group .btn:first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
    }

    .tax-classes-page .btn-group .btn:last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
    }

    /* Modal Max Height */
    .tax-classes-page .modal-body {
        max-height: 75vh;
        overflow-y: auto;
    }

    /* Responsive Mobile Optimization */
    @media (max-width: 575.98px) {
        .tax-classes-page {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        #createTaxClassModal .modal-dialog,
        #editTaxClassModal .modal-dialog,
        #deleteConfirmModal .modal-dialog {
            margin: 0.5rem;
        }

        #createTaxClassModal .modal-body,
        #editTaxClassModal .modal-body,
        #deleteConfirmModal .modal-body {
            padding: 0.75rem !important;
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