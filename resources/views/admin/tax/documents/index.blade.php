@extends('layouts.admin')

@section('title', 'Tax Documents Vault')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-3 tax-documents-wrapper">
  <!-- Header & Action Controls -->
<div class="tax-documents-header d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #e0f2fe; color: #0284c7; border-radius: 8px; font-size: 16px;">
            <i class="fas fa-file-shield"></i>
        </span>
        <div class="min-w-0 lh-sm">
            <h4 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 16px;">
                Tax Documents
            </h4>
            <div class="text-secondary text-truncate m-0 p-0" style="font-size: 11.5px;">
                NBR Audits &amp; Legal Documents.
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap flex-lg-nowrap justify-content-start justify-content-lg-end page-actions w-100 w-lg-auto">
        <a href="{{ route('admin.tax.dashboard') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 text-nowrap flex-shrink-0" style="height: 34px;">
            <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
        </a>
        <button type="button" class="btn btn-sm btn-primary px-3 d-inline-flex align-items-center gap-1 text-nowrap flex-shrink-0" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal" style="height: 34px;">
            <i class="fas fa-upload"></i> <span>Upload Document</span>
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

    <!-- Metric Status Cards -->
    <div class="row g-2 mb-3">
        <!-- 1. Total Documents -->
        <div class="col-6 col-md-3">
            <div class="card tax-metric-card border-0 shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Total Documents</span>
                        <div class="fw-bold text-dark metric-value font-monospace text-truncate mt-1">{{ $documents->total() }}</div>
                    </div>
                    <div class="p-2 rounded-1 bg-primary-subtle text-primary flex-shrink-0">
                        <i class="fas fa-folder-open fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Valid Documents -->
        <div class="col-6 col-md-3">
            <div class="card tax-metric-card border-0 shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Valid Records</span>
                        <div class="fw-bold text-success metric-value font-monospace text-truncate mt-1">
                            {{ $documents->where('is_expired', false)->count() }}
                        </div>
                    </div>
                    <div class="p-2 rounded-1 bg-success-subtle text-success flex-shrink-0">
                        <i class="fas fa-shield-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Expiring Soon -->
        <div class="col-6 col-md-3">
            <div class="card tax-metric-card border-0 shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Expiring (30 Days)</span>
                        <div class="fw-bold text-warning metric-value font-monospace text-truncate mt-1">
                            {{ $documents->filter(fn($d) => $d->expiry_date && \Carbon\Carbon::parse($d->expiry_date)->isBetween(now(), now()->addDays(30)))->count() }}
                        </div>
                    </div>
                    <div class="p-2 rounded-1 bg-warning-subtle text-warning flex-shrink-0">
                        <i class="fas fa-clock fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Expired Records -->
        <div class="col-6 col-md-3">
            <div class="card tax-metric-card border-0 shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="overflow-hidden">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate d-block" style="font-size: 11px; letter-spacing: 0.5px;">Expired Records</span>
                        <div class="fw-bold text-danger metric-value font-monospace text-truncate mt-1">
                            {{ $documents->where('is_expired', true)->count() }}
                        </div>
                    </div>
                    <div class="p-2 rounded-1 bg-danger-subtle text-danger flex-shrink-0">
                        <i class="fas fa-calendar-xmark fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card tax-documents-filter-card border-0 shadow-sm bg-white mb-4">
        <div class="card-body p-2 p-md-3">
            <form action="{{ route('admin.tax.documents.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title or reference..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Document Type Select -->
                <div class="col-6 col-md-3">
                    <select name="doc_type" class="form-select form-select-sm">
                        <option value="">All Document Types</option>
                        @foreach($docTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('doc_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="valid" {{ request('status') === 'valid' ? 'selected' : '' }}>Valid</option>
                        <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (&lt; 30 Days)</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-12 col-md-2 d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="fas fa-filter"></i> <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'doc_type', 'status']))
                        <a href="{{ route('admin.tax.documents.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters" aria-label="Reset Filters">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Data Table Card -->
    <div class="card tax-documents-table-card border-0 shadow-sm bg-white mb-4">
        <div class="table-responsive tax-documents-table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap tax-documents-table">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-3 py-2" style="width: 45px;">#</th>
                        <th class="py-2">Title &amp; Reference</th>
                        <th class="py-2">Type</th>
                        <th class="py-2">Entity / Vendor</th>
                        <th class="py-2">Issue &amp; Expiry</th>
                        <th class="text-center py-2">Validity</th>
                        <th class="text-center py-2">Document</th>
                        <th class="py-2">Uploaded By</th>
                        <th class="text-end pe-3 py-2" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $index => $doc)
                        <tr>
                            <td class="ps-3 font-monospace text-secondary">{{ $documents->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $doc->title }}">{{ $doc->title }}</div>
                                @if($doc->reference_no)
                                    <span class="badge bg-light text-secondary border font-monospace mt-1 px-1" style="font-size: 10.5px;">
                                        REF: {{ $doc->reference_no }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    {{ $docTypes[$doc->doc_type] ?? ucfirst(str_replace('_', ' ', $doc->doc_type)) }}
                                </span>
                            </td>
                            <td>
                                @php $meta = $doc->meta_data ?? []; @endphp
                                @if(!empty($meta['vendor_name']))
                                    <div class="fw-semibold text-dark">{{ $meta['vendor_name'] }}</div>
                                    @if(!empty($meta['vendor_bin']))
                                        <span class="text-secondary font-monospace d-block" style="font-size: 11px;">BIN: {{ $meta['vendor_bin'] }}</span>
                                    @endif
                                @else
                                    <span class="text-secondary small">Organization Record</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small">
                                    <i class="far fa-calendar-check text-success me-1"></i>
                                    {{ $doc->issue_date ? \Carbon\Carbon::parse($doc->issue_date)->format('d M Y') : 'N/A' }}
                                </div>
                                <div class="small {{ $doc->is_expired ? 'text-danger fw-bold' : 'text-secondary' }}" style="font-size: 11px;">
                                    <i class="far fa-calendar-xmark me-1"></i>
                                    {{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('d M Y') : 'No Expiry' }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($doc->expiry_date)
                                    @if($doc->is_expired)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace">Expired</span>
                                    @elseif(\Carbon\Carbon::parse($doc->expiry_date)->isBetween(now(), now()->addDays(30)))
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 font-monospace">Expiring Soon</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace">Valid</span>
                                    @endif
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 font-monospace">Permanent</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($doc->file_path)
                                    <a href="{{ route('admin.tax.documents.download', $doc->id) }}" class="btn btn-sm btn-light border py-1 px-2 d-inline-flex align-items-center gap-1" title="Download Document">
                                        <i class="fas fa-file-pdf text-primary" style="font-size: 11px;"></i> <span style="font-size: 11px;">File</span>
                                    </a>
                                @else
                                    <span class="text-secondary small">None</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark d-block fw-medium small">{{ $doc->uploader ? $doc->uploader->name : 'Admin' }}</span>
                                <span class="text-secondary" style="font-size: 10.5px;">{{ $doc->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info border-light-subtle py-1 px-2 view-doc-btn" data-id="{{ $doc->id }}" title="View details" aria-label="View details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary border-light-subtle py-1 px-2 edit-doc-btn" data-id="{{ $doc->id }}" title="Edit" aria-label="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger border-light-subtle py-1 px-2 delete-doc-btn" data-id="{{ $doc->id }}" data-title="{{ $doc->title }}" title="Delete" aria-label="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-secondary">
                                    <i class="fas fa-file-shield fs-2 opacity-50 mb-2 d-block"></i>
                                    <h6 class="fw-semibold text-dark">No Documents Found</h6>
                                    <p class="small mb-3">Upload a BIN certificate, trade license, or tax compliance record.</p>
                                    <button type="button" class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                        <i class="fas fa-upload me-1"></i> Upload First Document
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="card-footer bg-white border-0 py-2 px-3 d-flex justify-content-center justify-content-md-end">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Upload Document -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content tax-document-modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="uploadDocumentModalLabel">
                    <i class="fas fa-upload text-primary"></i> Upload Compliance Document
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tax.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Document Type <span class="text-danger">*</span></label>
                            <select name="doc_type" class="form-select form-select-sm" required>
                                <option value="">Select Type...</option>
                                @foreach($docTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Document Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="BIN Certificate 2026" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Reference No</label>
                            <input type="text" name="reference_no" class="form-control form-control-sm font-monospace" placeholder="BIN-001293847-0101">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Upload File <span class="text-danger">*</span></label>
                            <input type="file" name="file_path" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                            <span class="text-secondary d-block mt-1" style="font-size: 11px;">Max size: 10 MB. Supported formats: PDF, JPG, PNG, WEBP.</span>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control form-control-sm">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control form-control-sm">
                            <span class="text-secondary d-block mt-1" style="font-size: 11px;">Leave blank for permanent records without expiration.</span>
                        </div>

                        <!-- Vendor Meta -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Vendor / Supplier Name (Optional)</label>
                            <input type="text" name="vendor_name" class="form-control form-control-sm" placeholder="Textile Mills Ltd">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Vendor BIN / TIN (Optional)</label>
                            <input type="text" name="vendor_bin" class="form-control form-control-sm font-monospace" placeholder="110928374-0202">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Audit Notes / Remarks</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Enter relevant audit notes or archival references..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="fas fa-save"></i> <span>Save Document</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Document -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-labelledby="editDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content tax-document-modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="editDocumentModalLabel">
                    <i class="fas fa-edit text-primary"></i> Edit Document Record
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDocumentForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Document Type <span class="text-danger">*</span></label>
                            <select name="doc_type" class="form-select form-select-sm" id="editDocType" required>
                                @foreach($docTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Document Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" id="editDocTitle" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Reference No</label>
                            <input type="text" name="reference_no" class="form-control form-control-sm font-monospace" id="editDocReferenceNo">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Replace File (Optional)</label>
                            <input type="file" name="file_path" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <div id="editCurrentFileDisplay" class="mt-1 small"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control form-control-sm" id="editIssueDate">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control form-control-sm" id="editExpiryDate">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Vendor Name</label>
                            <input type="text" name="vendor_name" class="form-control form-control-sm" id="editVendorName">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Vendor BIN / TIN</label>
                            <input type="text" name="vendor_bin" class="form-control form-control-sm font-monospace" id="editVendorBin">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Administrative Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" id="editDocNotes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1">
                        <i class="fas fa-check-circle"></i> <span>Update Document</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: View Document Details -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tax-document-modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="viewDocumentModalLabel">
                    <i class="fas fa-file-shield text-info"></i> Document Details
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="viewDocumentModalBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-4 text-primary"></i></div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Global Hidden Delete Form -->
<form id="globalDeleteDocForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
    /* Tax Documents Vault — Kaiadmin Lite inspired White Classic UI */
    .tax-documents-wrapper {
        --tax-doc-border: #e6e9ef;
        --tax-doc-muted: #667085;
        --tax-doc-primary: var(--bs-primary, #1572e8);
        color: #2a2f3a;
        min-width: 0;
    }

    .tax-documents-wrapper .card,
    .tax-documents-wrapper .btn,
    .tax-documents-wrapper .form-control,
    .tax-documents-wrapper .form-select,
    .tax-documents-wrapper .input-group-text,
    .tax-documents-wrapper .badge,
    .tax-documents-wrapper .alert,
    .tax-documents-wrapper .rounded-1,
    .tax-document-modal-content,
    .tax-document-modal-content .btn,
    .tax-document-modal-content .form-control,
    .tax-document-modal-content .form-select,
    .tax-document-modal-content .input-group-text,
    .tax-document-modal-content .badge {
        border-radius: 5px !important;
    }

    .tax-documents-wrapper .card,
    .tax-document-modal-content {
        border: 1px solid var(--tax-doc-border) !important;
        background: #fff;
        box-shadow: 0 2px 8px rgba(28, 39, 60, .045) !important;
        overflow: hidden;
    }

    .tax-documents-wrapper .tax-documents-header h4 {
        font-size: clamp(1.1rem, 1.5vw, 1.35rem);
        letter-spacing: -.025em;
    }

    .tax-documents-wrapper .tax-documents-header p {
        line-height: 1.55;
        max-width: 760px;
    }

    .tax-documents-wrapper .btn,
    .tax-document-modal-content .btn {
        min-height: 34px;
        font-weight: 600;
        letter-spacing: .005em;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .tax-documents-wrapper .btn:focus-visible,
    .tax-document-modal-content .btn:focus-visible,
    .tax-documents-wrapper .form-control:focus,
    .tax-documents-wrapper .form-select:focus,
    .tax-document-modal-content .form-control:focus,
    .tax-document-modal-content .form-select:focus {
        box-shadow: 0 0 0 .2rem rgba(21, 114, 232, .12);
    }

    .tax-documents-wrapper .form-control,
    .tax-documents-wrapper .form-select,
    .tax-document-modal-content .form-control,
    .tax-document-modal-content .form-select {
        min-height: 38px;
        border-color: #dfe3eb;
        color: #344054;
        font-size: .875rem;
    }

    .tax-documents-wrapper .form-control::placeholder,
    .tax-document-modal-content .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    .tax-documents-wrapper .input-group-text {
        border-color: #dfe3eb;
    }

    .tax-documents-wrapper .tax-metric-card {
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .tax-documents-wrapper .tax-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(28, 39, 60, .075) !important;
    }

    .tax-documents-wrapper .metric-value {
        font-size: clamp(1.2rem, 1.8vw, 1.55rem);
        line-height: 1.2;
        letter-spacing: -.025em;
    }

    .tax-documents-wrapper .tax-documents-filter-card .card-body {
        padding: 1rem;
    }

    .tax-documents-wrapper .tax-documents-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tax-documents-wrapper .tax-documents-table {
        width: 100%;
        min-width: 1050px;
        font-size: 12.5px;
        color: #475467;
        border-color: #edf0f4;
    }

    .tax-documents-wrapper .tax-documents-table thead th {
        padding: .8rem .7rem;
        background: #f8f9fc;
        color: #667085;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--tax-doc-border);
    }

    .tax-documents-wrapper .tax-documents-table tbody td {
        padding: .75rem .7rem;
        vertical-align: middle;
        border-color: #edf0f4;
    }

    .tax-documents-wrapper .tax-documents-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .tax-documents-wrapper .tax-documents-table tbody tr:hover {
        --bs-table-accent-bg: #f8faff;
    }

    .tax-documents-wrapper .tax-documents-table .badge {
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.4;
        white-space: nowrap;
    }

    .tax-documents-wrapper .tax-documents-table .btn-group .btn {
        min-height: 30px;
        padding: .25rem .5rem;
    }

    .tax-documents-wrapper .btn-group .btn:first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
    }

    .tax-documents-wrapper .btn-group .btn:last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
    }

    .tax-documents-wrapper .tax-documents-table-card .card-footer {
        border-top: 1px solid var(--tax-doc-border) !important;
    }

    .tax-documents-wrapper .pagination {
        gap: .25rem;
        margin-bottom: 0;
    }

    .tax-documents-wrapper .page-link {
        border-radius: 5px !important;
        font-size: .8rem;
    }

    .tax-document-modal-content {
        overflow: hidden;
    }

    .tax-document-modal-content .modal-header {
        background: #fff;
        border-color: var(--tax-doc-border) !important;
    }

    .tax-document-modal-content .modal-title {
        font-size: 1rem;
    }

    .tax-document-modal-content .modal-body {
        max-height: 72vh;
        overflow-y: auto;
    }

    .tax-document-modal-content .form-label {
        font-size: .8rem;
        margin-bottom: .4rem;
    }

    .tax-document-modal-content .form-control {
        min-height: 40px;
    }

    .tax-document-modal-content textarea.form-control {
        min-height: 88px;
    }

    .tax-document-modal-content .list-group-item {
        border-color: #edf0f4;
        gap: .75rem;
    }

    .tax-document-modal-content .list-group-item > :last-child {
        text-align: right;
        overflow-wrap: anywhere;
    }

    @media (min-width: 1200px) {
        .tax-documents-wrapper .tax-documents-table {
            min-width: 0;
        }

        .tax-documents-wrapper .tax-documents-table thead th,
        .tax-documents-wrapper .tax-documents-table tbody td {
            padding-left: .55rem;
            padding-right: .55rem;
        }
    }

    @media (max-width: 767.98px) {
        .tax-documents-wrapper {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .tax-documents-wrapper .tax-documents-header {
            align-items: stretch !important;
        }

        .tax-documents-wrapper .page-actions {
            width: 100%;
        }

        .tax-documents-wrapper .tax-documents-filter-card .card-body {
            padding: .85rem;
        }

        .tax-documents-wrapper .tax-documents-table {
            min-width: 1050px;
        }

        .tax-documents-wrapper .tax-documents-table-card .card-footer {
            padding: .75rem !important;
        }

        .tax-documents-wrapper .tax-metric-card .card-body {
            padding: .8rem !important;
        }
    }

    @media (max-width: 575.98px) {
        .tax-documents-wrapper .tax-documents-header .page-actions .btn {
            flex: 1 1 0;
            justify-content: center;
            white-space: nowrap;
        }

        #uploadDocumentModal .modal-dialog,
        #editDocumentModal .modal-dialog,
        #viewDocumentModal .modal-dialog {
            margin: .5rem;
        }

        #uploadDocumentModal .tax-document-modal-content .modal-body,
        #editDocumentModal .tax-document-modal-content .modal-body,
        #viewDocumentModal .tax-document-modal-content .modal-body {
            padding: 1rem !important;
        }

        #uploadDocumentModal .tax-document-modal-content .modal-header,
        #editDocumentModal .tax-document-modal-content .modal-header,
        #viewDocumentModal .tax-document-modal-content .modal-header {
            padding: .85rem 1rem !important;
        }

        #uploadDocumentModal .tax-document-modal-content .modal-footer,
        #editDocumentModal .tax-document-modal-content .modal-footer,
        #viewDocumentModal .tax-document-modal-content .modal-footer {
            padding: 0 1rem 1rem !important;
        }

        .tax-documents-wrapper .metric-value {
            font-size: 1.15rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModal = new bootstrap.Modal(document.getElementById('editDocumentModal'));
        const viewModal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));

        // 1. Edit button handler (AJAX)
        document.querySelectorAll('.edit-doc-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;

                fetch(`{{ url('admin/tax/documents/show') }}/${id}`)
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) {
                            alert('Failed to load document details.');
                            return;
                        }
                        const d = res.data;
                        document.getElementById('editDocType').value = d.doc_type || '';
                        document.getElementById('editDocTitle').value = d.title || '';
                        document.getElementById('editDocReferenceNo').value = d.reference_no || '';
                        document.getElementById('editIssueDate').value = d.issue_date ? d.issue_date.split('T')[0] : '';
                        document.getElementById('editExpiryDate').value = d.expiry_date ? d.expiry_date.split('T')[0] : '';

                        const meta = d.meta_data || {};
                        document.getElementById('editVendorName').value = meta.vendor_name || '';
                        document.getElementById('editVendorBin').value = meta.vendor_bin || '';
                        document.getElementById('editDocNotes').value = meta.notes || '';

                        const fileBox = document.getElementById('editCurrentFileDisplay');
                        if (d.file_path) {
                            fileBox.innerHTML = `<span class="badge bg-light text-primary border px-2 py-1"><i class="fas fa-file-check me-1"></i> Current file archived</span>`;
                        } else {
                            fileBox.innerHTML = '';
                        }

                        const form = document.getElementById('editDocumentForm');
                        form.action = `{{ url('admin/tax/documents/update') }}/${id}`;
                        editModal.show();
                    })
                    .catch(() => alert('Server error: Failed to retrieve data.'));
            });
        });

        // 2. View button handler (AJAX)
        document.querySelectorAll('.view-doc-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const body = document.getElementById('viewDocumentModalBody');
                body.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-4 text-primary"></i></div>';
                viewModal.show();

                fetch(`{{ url('admin/tax/documents/show') }}/${id}`)
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) {
                            body.innerHTML = '<div class="text-danger text-center">Failed to load document details.</div>';
                            return;
                        }
                        const d = res.data;
                        const meta = d.meta_data || {};
                        body.innerHTML = `
                            <div class="list-group list-group-flush" style="font-size: 12.5px;">
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Title:</span>
                                    <span class="fw-semibold text-dark">${d.title}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Type:</span>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1">${d.doc_type}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Reference No:</span>
                                    <span class="font-monospace text-dark fw-semibold">${d.reference_no || 'N/A'}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Issue Date:</span>
                                    <span class="text-dark">${d.issue_date ? d.issue_date.split('T')[0] : 'N/A'}</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="text-secondary">Expiry Date:</span>
                                    <span class="${d.is_expired ? 'text-danger fw-bold' : 'text-dark'}">${d.expiry_date ? d.expiry_date.split('T')[0] : 'No Expiry'}</span>
                                </div>
                                ${meta.vendor_name ? `
                                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="text-secondary">Vendor:</span>
                                        <span class="text-dark fw-medium">${meta.vendor_name} (BIN: ${meta.vendor_bin || 'N/A'})</span>
                                    </div>
                                ` : ''}
                                ${d.file_path ? `
                                    <div class="list-group-item d-flex justify-content-between px-0 py-2 align-items-center">
                                        <span class="text-secondary">Attachment:</span>
                                        <a href="{{ url('admin/tax/documents/download') }}/${d.id}" class="btn btn-sm btn-primary py-1 px-2 d-inline-flex align-items-center gap-1">
                                            <i class="fas fa-download"></i> <span>Download</span>
                                        </a>
                                    </div>
                                ` : ''}
                                ${meta.notes ? `
                                    <div class="list-group-item px-0 py-2">
                                        <span class="text-secondary d-block mb-1">Administrative Notes:</span>
                                        <div class="p-2 bg-light border border-light-subtle rounded-1 text-dark small">${meta.notes}</div>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    })
                    .catch(() => {
                        body.innerHTML = '<div class="text-danger text-center">Server error: Failed to retrieve data.</div>';
                    });
            });
        });

        // 3. Delete confirmation handler
        document.querySelectorAll('.delete-doc-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const title = this.dataset.title;

                if (confirm(`Are you sure you want to permanently delete the document "${title}"?`)) {
                    const form = document.getElementById('globalDeleteDocForm');
                    form.action = `{{ url('admin/tax/documents/delete') }}/${id}`;
                    form.submit();
                }
            });
        });
    });
</script>
@endsection