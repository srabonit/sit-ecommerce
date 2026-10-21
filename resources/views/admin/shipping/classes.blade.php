@extends('layouts.admin')
@section('title', 'Shipping Classes - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #shippingClassesRoot .btn,
    #shippingClassesRoot .btn-sm,
    #shippingClassesRoot button,
    #shippingClassesRoot .form-control,
    #shippingClassesRoot .form-select,
    #shippingClassesRoot .input-group-text,
    #shippingClassesRoot .nav-link,
    #shippingClassesRoot .alert,
    #shippingClassesRoot .badge,
    #shippingClassesRoot .class-preset-btn,
    #deleteShippingClassModal .btn,
    #deleteShippingClassModal .modal-content {
        border-radius: 5px !important;
    }

    #shippingClassesRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #shippingClassesRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #shippingClassesRoot .btn-compact,
    #deleteShippingClassModal .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .class-action-sq-btn {
        width: 27px !important;
        height: 27px !important;
        min-width: 27px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Cards */
    .class-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .class-panel-header {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Segmented Sub-Navigation Bar */
    .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }
    .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }
    .shipping-nav-pills {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 6px !important;
        display: inline-flex;
        gap: 4px;
    }
    .shipping-nav-pills .nav-link {
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 14px;
        transition: all 0.15s ease;
        background: transparent;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .shipping-nav-pills .nav-link:hover {
        color: #0f172a;
        background-color: rgba(255, 255, 255, 0.6);
    }
    .shipping-nav-pills .nav-link.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    /* Form Controls */
    #shippingClassesRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #shippingClassesRoot .form-control {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #shippingClassesRoot .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Quick Preset Chips */
    .class-preset-btn {
        font-size: 10.5px;
        font-weight: 600;
        padding: 3px 8px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.12s ease;
    }
    .class-preset-btn:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: #eff6ff;
    }

    /* Table Styling */
    .custom-classes-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 10px 14px;
        white-space: nowrap;
    }
    .custom-classes-table td {
        padding: 11px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-classes-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    @media (max-width: 767.98px) {
        .shipping-nav-pills {
            display: flex;
            min-width: max-content;
        }
        .shipping-nav-pills .nav-link {
            padding: 6px 12px;
            font-size: 11.5px;
        }
        .custom-classes-table th,
        .custom-classes-table td {
            padding: 9px 10px !important;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="shippingClassesRoot">
    <!-- Sub-Menu Navigation (Mobile Scrollable) -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Shipping zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.settings') }}">
                    <i class="fas fa-sliders-h" style="font-size: 11px;"></i>
                    <span>Shipping settings</span>
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
                    <span>Local pickup</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-boxes text-primary"></i>
                    <span>Shipping Classes</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" style="font-size: 11px;">
                    {{ count($classes) }} Classes
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Group products of similar type to provide specific shipping rates ( Heavy, Bulky, or Fragile goods).</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between" role="alert">
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 g-md-4">
        <!-- Left Column: Add Shipping Class Form -->
        <div class="col-12 col-lg-4">
            <div class="class-panel-card mb-3">
                <div class="class-panel-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                        <i class="fas fa-plus-circle text-primary"></i>
                        <span>Add Shipping Class</span>
                    </h6>
                    <small class="text-muted" style="font-size: 10.5px;">New Class</small>
                </div>

                <div class="p-3 p-md-4">
                    <!-- Quick Class Presets -->
                    <div class="mb-3 p-2 bg-light border" style="border-radius: 5px;">
                        <small class="text-muted fw-bold d-block mb-1.5" style="font-size: 10.5px;">
                            <i class="fas fa-bolt text-warning me-1"></i> Quick Presets:
                        </small>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="class-preset-btn" onclick="applyClassPreset('Heavy Items', 'Products over 5 KG requiring extra handling fee')">Heavy Items</span>
                            <span class="class-preset-btn" onclick="applyClassPreset('Bulky Goods', 'Large dimensional packages like furniture or electronics')">Bulky Goods</span>
                            <span class="class-preset-btn" onclick="applyClassPreset('Fragile', 'Glassware or delicate products needing bubble wrap')">Fragile</span>
                            <span class="class-preset-btn" onclick="applyClassPreset('Express Standard', 'Standard lightweight clothing and parcel items')">Express Standard</span>
                        </div>
                    </div>

                    <form action="{{ route('admin.shipping.class.store') }}" method="POST" id="addClassForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="classNameInput">Shipping class name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="classNameInput" class="form-control fw-semibold" placeholder=" Heavy Items" required>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Unique title shown when setting shipping rates.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="classDescInput">Description</label>
                            <textarea name="description" id="classDescInput" class="form-control" rows="3" placeholder="Brief summary describing the type of products in this class..."></textarea>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Optional reference note for store managers.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-compact w-100 py-2 shadow-xs" style="font-size: 12.5px !important;">
                            <i class="fas fa-check-circle me-1"></i>
                            <span>Create class</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Classes Data Table -->
        <div class="col-12 col-lg-8">
            <div class="class-panel-card">
                <!-- Search & Filter Bar -->
                <div class="p-2.5 px-3 bg-light border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
                    <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                        <i class="fas fa-list text-primary me-1"></i> Active Classes Directory
                    </span>
                    <div style="max-width: 260px;" class="w-100">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0" style="height: 30px;"><i class="fas fa-search text-muted" style="font-size: 10.5px;"></i></span>
                            <input type="text" id="classSearchInput" class="form-control border-start-0" style="height: 30px; font-size: 11.5px;" placeholder="Search class or slug..." oninput="filterClassesTable(this.value)">
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
                    <table class="table custom-classes-table align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th style="min-width: 140px;">Shipping class</th>
                                <th style="min-width: 120px;">Slug</th>
                                <th style="min-width: 200px;">Description</th>
                                <th class="text-end pe-3" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="classesTbody">
                            @forelse($classes as $cls)
                                <tr class="class-row-item" data-search="{{ strtolower($cls->name . ' ' . $cls->slug . ' ' . ($cls->description ?? '')) }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-box text-primary opacity-75"></i>
                                            <strong class="text-dark" style="font-size: 13px;">{{ $cls->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 11px;">
                                            {{ $cls->slug }}
                                        </span>
                                    </td>
                                    <td class="small text-muted text-wrap" style="max-width: 280px; font-size: 12px; line-height: 1.4;">
                                        {{ $cls->description ?: '—' }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-outline-danger class-action-sq-btn shadow-xs" title="Delete Class" onclick="openDeleteClassModal('{{ route('admin.shipping.class.destroy', $cls->id) }}', '{{ addslashes($cls->name) }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">
                                        <i class="fas fa-boxes fa-2x mb-2 d-block opacity-25"></i>
                                        <span class="fw-semibold">No shipping classes created yet.</span>
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

<!-- Delete Confirmation Modal (Replaces native confirm()) -->
<div class="modal fade" id="deleteShippingClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 46px; height: 46px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Shipping Class?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to delete <strong id="deleteClassNameText" class="text-dark"></strong>? Products assigned to this class will be unassigned.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteShippingClassForm" method="POST" action="" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact px-3 shadow-xs">Delete</button>
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