
@extends('layouts.admin')
@section('title', 'Shipping Classes - ' . config('app.name'))

@section('admin_content')
<style>
    /* Kaiadmin Classic - Shipping Classes */
    #shippingClassesRoot {
        --sc-primary: var(--bs-primary, #1572e8);
        --sc-border: #e6e9ef;
        --sc-heading: #2a2f3a;
        --sc-text: #495057;
        --sc-muted: #8d949d;
        --sc-bg: #f8f9fa;
        color: var(--sc-text);
        font-size: 13px;
    }

    #shippingClassesRoot *,
    #deleteShippingClassModal * {
        box-sizing: border-box;
    }

    #shippingClassesRoot .btn,
    #shippingClassesRoot .form-control,
    #shippingClassesRoot .input-group-text,
    #shippingClassesRoot .badge,
    #shippingClassesRoot .nav-link,
    #shippingClassesRoot .class-preset-btn,
    #deleteShippingClassModal .btn,
    #deleteShippingClassModal .modal-content {
        border-radius: 5px !important;
    }

    /* Navigation */
    #shippingClassesRoot .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    #shippingClassesRoot .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }

    #shippingClassesRoot .shipping-nav-pills {
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 3px;
        padding: 4px;
        background: var(--sc-bg);
        border: 1px solid var(--sc-border);
        border-radius: 6px;
    }

    #shippingClassesRoot .shipping-nav-pills .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 13px;
        color: #6b7280;
        background: transparent;
        font-size: 12px;
        font-weight: 600;
        transition: all .15s ease;
    }

    #shippingClassesRoot .shipping-nav-pills .nav-link:hover {
        color: var(--sc-primary);
        background: #fff;
    }

    #shippingClassesRoot .shipping-nav-pills .nav-link.active {
        color: #fff;
        background: var(--sc-primary);
        box-shadow: 0 2px 5px rgba(21, 114, 232, .16);
    }

    /* Page Header */
    #shippingClassesRoot .page-heading {
        color: var(--sc-heading);
        font-size: clamp(16px, 2vw, 19px);
        font-weight: 700;
        letter-spacing: -.2px;
    }

    #shippingClassesRoot .page-subtitle {
        color: var(--sc-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    #shippingClassesRoot .total-badge {
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 5px;
    }

    /* Cards */
    #shippingClassesRoot .class-panel-card {
        height: 100%;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--sc-border);
        border-radius: 7px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
    }

    #shippingClassesRoot .class-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 48px;
        padding: 11px 15px;
        background: #fff;
        border-bottom: 1px solid var(--sc-border);
    }

    #shippingClassesRoot .panel-title {
        margin: 0;
        color: var(--sc-heading);
        font-size: 13px;
        font-weight: 700;
    }

    #shippingClassesRoot .panel-content {
        padding: 16px;
    }

    /* Buttons */
    #shippingClassesRoot .btn-compact,
    #deleteShippingClassModal .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.3;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #shippingClassesRoot .btn-primary {
        background: var(--sc-primary);
        border-color: var(--sc-primary);
    }

    #shippingClassesRoot .btn-primary:hover {
        filter: brightness(.94);
    }

    #shippingClassesRoot .class-action-sq-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        padding: 0 !important;
        border-radius: 5px !important;
        font-size: 12px !important;
    }

    /* Form */
    #shippingClassesRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
    }

    #shippingClassesRoot .form-control {
        min-height: 36px;
        padding: 7px 10px;
        color: var(--sc-heading);
        background-color: #fff;
        border: 1px solid #dee2e6;
        font-size: 12.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #shippingClassesRoot .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #shippingClassesRoot .form-control:focus {
        border-color: var(--sc-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #shippingClassesRoot textarea.form-control {
        min-height: 84px;
        resize: vertical;
    }

    #shippingClassesRoot .form-hint {
        display: block;
        margin-top: 5px;
        color: var(--sc-muted);
        font-size: 11px;
        line-height: 1.4;
    }

    /* Presets */
    #shippingClassesRoot .preset-panel {
        padding: 11px;
        background: #f8f9fa;
        border: 1px solid var(--sc-border);
        border-radius: 5px;
    }

    #shippingClassesRoot .preset-heading {
        display: block;
        margin-bottom: 8px;
        color: #6c757d;
        font-size: 11px;
        font-weight: 700;
    }

    #shippingClassesRoot .class-preset-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 27px;
        padding: 4px 9px;
        color: #5c6470;
        background: #fff;
        border: 1px solid #dee2e6;
        font-size: 10.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
    }

    #shippingClassesRoot .class-preset-btn:hover {
        color: var(--sc-primary);
        border-color: var(--sc-primary);
        background: #f5f9ff;
    }

    /* Search */
    #shippingClassesRoot .class-search {
        width: 100%;
        max-width: 250px;
    }

    #shippingClassesRoot .class-search .input-group-text {
        min-height: 34px;
        padding: 6px 10px;
        background: #fff;
        border-color: #dee2e6;
    }

    #shippingClassesRoot .class-search .form-control {
        min-height: 34px;
        padding: 6px 9px;
        font-size: 12px;
    }

    /* Table */
    #shippingClassesRoot .classes-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #shippingClassesRoot .custom-classes-table {
        width: 100%;
        min-width: 590px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #shippingClassesRoot .custom-classes-table thead th {
        padding: 11px 13px;
        color: #6c757d;
        background: #f8f9fa;
        border-bottom: 1px solid var(--sc-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #shippingClassesRoot .custom-classes-table tbody td {
        padding: 11px 13px;
        color: #495057;
        border-bottom: 1px solid #f0f1f3;
        font-size: 12px;
        vertical-align: middle;
    }

    #shippingClassesRoot .custom-classes-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #shippingClassesRoot .custom-classes-table tbody tr:hover {
        background: #fafbfd;
    }

    #shippingClassesRoot .class-name {
        color: var(--sc-heading);
        font-size: 12.5px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    #shippingClassesRoot .class-slug {
        display: inline-block;
        max-width: 100%;
        padding: 5px 8px;
        color: #6c757d;
        background: #f8f9fa;
        border: 1px solid var(--sc-border);
        border-radius: 4px;
        font-size: 10.5px;
        overflow-wrap: anywhere;
    }

    #shippingClassesRoot .class-description {
        max-width: 300px;
        color: #8d949d;
        font-size: 11.5px;
        line-height: 1.5;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #shippingClassesRoot .bn-digit,
    #shippingClassesRoot .font-monospace {
        font-weight: 700 !important;
    }

    /* Alerts */
    #shippingClassesRoot .alert {
        border-radius: 5px;
        font-size: 12px;
    }

    /* Delete Modal */
    #deleteShippingClassModal .modal-content {
        border: 1px solid var(--sc-border);
        box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
    }

    #deleteShippingClassModal .modal-title-text {
        color: var(--sc-heading);
        font-size: 14px;
        font-weight: 700;
    }

    #deleteShippingClassModal .modal-message {
        color: #8d949d;
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #shippingClassesRoot .class-panel-card {
            height: auto;
        }
    }

    @media (max-width: 767.98px) {
        #shippingClassesRoot {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #shippingClassesRoot .shipping-nav-pills {
            display: flex;
            min-width: max-content;
        }

        #shippingClassesRoot .shipping-nav-pills .nav-link {
            padding: 7px 11px;
            font-size: 11.5px;
        }

        #shippingClassesRoot .class-panel-header {
            padding: 11px 13px;
        }

        #shippingClassesRoot .panel-content {
            padding: 13px;
        }

        #shippingClassesRoot .class-search {
            max-width: none;
        }

        #shippingClassesRoot .custom-classes-table {
            min-width: 590px;
        }

        #shippingClassesRoot .custom-classes-table thead th,
        #shippingClassesRoot .custom-classes-table tbody td {
            padding: 10px;
        }
    }

    @media (max-width: 575.98px) {
        #shippingClassesRoot .page-header-row {
            align-items: flex-start !important;
        }

        #shippingClassesRoot .page-subtitle {
            max-width: 290px;
        }

        #shippingClassesRoot .preset-panel {
            padding: 9px;
        }

        #deleteShippingClassModal .modal-dialog {
            margin: 12px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="shippingClassesRoot">
    <!-- Shipping Navigation -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.settings') }}">
                    <i class="fas fa-sliders-h" style="font-size: 11px;"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.shipping.classes') }}">
                    <i class="fas fa-boxes" style="font-size: 11px;"></i>
                    <span>Classes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.pickup') }}">
                    <i class="fas fa-store" style="font-size: 11px;"></i>
                    <span>Pickup</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 page-header-row">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h5 class="page-heading mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-boxes text-primary"></i>
                    <span>Shipping Classes</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle total-badge bn-digit font-monospace">
                    {{ count($classes) }} Total
                </span>
            </div>
            <small class="page-subtitle d-block mt-1">Set rates by product type.</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Add Class -->
        <div class="col-12 col-lg-4">
            <div class="class-panel-card">
                <div class="class-panel-header">
                    <h6 class="panel-title d-flex align-items-center gap-2">
                        <i class="fas fa-plus-circle text-primary"></i>
                        <span>Add Class</span>
                    </h6>
                    <small class="text-muted" style="font-size: 11px;">New</small>
                </div>

                <div class="panel-content">
                    <!-- Quick Presets -->
                    <div class="preset-panel mb-3">
                        <small class="preset-heading">
                            <i class="fas fa-bolt text-warning me-1"></i> Quick Presets
                        </small>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="class-preset-btn" onclick="applyClassPreset('Heavy Items', 'Products over 5 KG requiring extra handling fee')">Heavy</span>
                            <span class="class-preset-btn" onclick="applyClassPreset('Bulky Goods', 'Large dimensional packages like furniture or electronics')">Bulky</span>
                            <span class="class-preset-btn" onclick="applyClassPreset('Fragile', 'Glassware or delicate products needing bubble wrap')">Fragile</span>
                            <span class="class-preset-btn" onclick="applyClassPreset('Express Standard', 'Standard lightweight clothing and parcel items')">Standard</span>
                        </div>
                    </div>

                    <form action="{{ route('admin.shipping.class.store') }}" method="POST" id="addClassForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="classNameInput">Class Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="classNameInput" class="form-control fw-semibold" placeholder="Heavy Items" required>
                            <small class="form-hint">Unique name.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="classDescInput">Description</label>
                            <textarea name="description" id="classDescInput" class="form-control" rows="3" placeholder="Short description..."></textarea>
                            <small class="form-hint">Optional.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-compact w-100 py-2 shadow-sm">
                            <i class="fas fa-check-circle"></i>
                            <span>Create Class</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Class List -->
        <div class="col-12 col-lg-8">
            <div class="class-panel-card">
                <!-- Search -->
                <div class="p-3 bg-white border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
                    <span class="fw-bold text-dark" style="font-size: 12.5px;">
                        <i class="fas fa-list text-primary me-1"></i> Class List
                    </span>
                    <div class="class-search">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0">
                                <i class="fas fa-search text-muted" style="font-size: 11px;"></i>
                            </span>
                            <input type="text" id="classSearchInput" class="form-control border-start-0" placeholder="Search name or slug..." oninput="filterClassesTable(this.value)">
                        </div>
                    </div>
                </div>

                <div class="classes-table-wrap">
                    <table class="table custom-classes-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 140px;">Class</th>
                                <th style="min-width: 120px;">Slug</th>
                                <th style="min-width: 200px;">Description</th>
                                <th class="text-end pe-3" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="classesTbody">
                            @forelse($classes as $cls)
                                <tr class="class-row-item" data-search="{{ strtolower($cls->name . ' ' . $cls->slug . ' ' . ($cls->description ?? '')) }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-box text-primary opacity-75"></i>
                                            <strong class="class-name">{{ $cls->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="class-slug font-monospace">
                                            {{ $cls->slug }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="class-description">
                                            {{ $cls->description ?: '—' }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-outline-danger class-action-sq-btn shadow-sm" title="Delete" onclick="openDeleteClassModal('{{ route('admin.shipping.class.destroy', $cls->id) }}', '{{ addslashes($cls->name) }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">
                                        <i class="fas fa-boxes fa-2x mb-2 d-block opacity-25"></i>
                                        <span class="fw-semibold">No classes yet.</span>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteShippingClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="modal-title-text mb-1">Delete Class?</h6>
                <p class="modal-message mb-3">
                    Delete <strong id="deleteClassNameText" class="text-dark"></strong>? Assigned products will be unassigned.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteShippingClassForm" method="POST" action="" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact px-3 shadow-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function applyClassPreset(name, desc) {
        document.getElementById('classNameInput').value = name;
        document.getElementById('classDescInput').value = desc;
        document.getElementById('classNameInput').focus();
    }

    function openDeleteClassModal(actionUrl, className) {
        document.getElementById('deleteShippingClassForm').action = actionUrl;
        document.getElementById('deleteClassNameText').innerText = '"' + className + '"';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteShippingClassModal')).show();
    }

    function filterClassesTable(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.class-row-item').forEach(row => {
            const searchStr = row.getAttribute('data-search') || '';
            row.style.display = (!q || searchStr.includes(q)) ? '' : 'none';
        });
    }
</script>
@endsection
