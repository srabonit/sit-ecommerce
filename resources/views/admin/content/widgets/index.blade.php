@extends('layouts.admin')

@section('title', 'Widgets Manager - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #widgetsManagerRoot .btn,
    #widgetsManagerRoot .btn-sm,
    #widgetsManagerRoot button,
    #widgetsManagerRoot .badge,
    #widgetsManagerRoot .alert,
    #addWidgetModal .btn,
    #addWidgetModal .form-control,
    #addWidgetModal .form-select,
    #editWidgetModal .btn,
    #editWidgetModal .form-control,
    #editWidgetModal .form-select,
    #deleteWidgetModal .btn {
        border-radius: 5px !important;
    }

    /* Compact & Sleek Button Sizing */
    #widgetsManagerRoot .btn-compact,
    #addWidgetModal .btn-compact,
    #editWidgetModal .btn-compact,
    #deleteWidgetModal .btn-compact {
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

    #widgetsManagerRoot .btn-icon-sq {
        width: 26px !important;
        height: 26px !important;
        padding: 0 !important;
        font-size: 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        transition: all 0.15s ease;
    }
    #widgetsManagerRoot .btn-icon-sq:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    /* Zone Container Card */
    .zone-column-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .zone-column-header {
        background: #f8fafc;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 7px 7px 0 0;
    }

    /* Individual Widget Item Card */
    .widget-item-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px !important;
        padding: 12px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .widget-item-card:hover {
        border-color: #94a3b8;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
    }

    .widget-code-preview {
        font-size: 11px;
        max-height: 46px;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 6px 8px;
        color: #475569;
    }

    /* Status Toggle Badge Button */
    .btn-status-toggle {
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 3px 8px !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        cursor: pointer;
        border: none;
        transition: opacity 0.15s ease;
    }
    .btn-status-toggle:hover {
        opacity: 0.9;
    }
    .status-active   { background-color: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .status-inactive { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1 !important; }

    /* Modal Form Styling */
    #addWidgetModal .modal-content,
    #editWidgetModal .modal-content,
    #deleteWidgetModal .modal-content {
        border-radius: 8px !important;
        border: none;
    }
    #addWidgetModal .form-label,
    #editWidgetModal .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }
    #addWidgetModal .form-control,
    #addWidgetModal .form-select,
    #editWidgetModal .form-control,
    #editWidgetModal .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #addWidgetModal .form-control:focus,
    #addWidgetModal .form-select:focus,
    #editWidgetModal .form-control:focus,
    #editWidgetModal .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .widget-index-header h5 {
            font-size: 16px !important;
        }
        .btn-add-widget {
            width: 100%;
            justify-content: center;
            padding: 7px 12px !important;
        }
        .zone-column-header {
            padding: 10px 14px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="widgetsManagerRoot">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 widget-index-header">
        <div>
            <h5 class="fw-bold text-dark mb-1" style="font-size: 18px;">Widgets Management</h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Manage sidebar, footer, HTML/JS & banners.</small>
        </div>
        <button type="button" class="btn btn-primary btn-compact shadow-xs align-self-start align-self-sm-auto btn-add-widget" data-bs-toggle="modal" data-bs-target="#addWidgetModal">
            <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
            <span>Add New Widget</span>
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <span class="fw-semibold">{{ session('success') }}</span>
            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Widget Zones Grid -->
    <div class="row g-3">
        @php
            $zones = [
                'sidebar-right' => 'Right Sidebar (Blog & Single Pages)',
                'sidebar-left'  => 'Left Sidebar (Support / Extra)',
                'footer-widgets'=> 'Footer Area Widgets'
            ];
        @endphp

        @foreach($zones as $zoneKey => $zoneName)
            @php
                $zoneWidgets = $widgets->where('widget_zone', $zoneKey);
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="zone-column-card h-100 d-flex flex-column">
                    <div class="zone-column-header d-flex justify-content-between align-items-center">
                        <div class="text-truncate pe-2">
                            <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 13.5px;" title="{{ $zoneName }}">{{ $zoneName }}</h6>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-1" style="font-size: 10px;">zone: {{ $zoneKey }}</span>
                        </div>
                        <span class="badge bg-primary text-white px-2 py-1 flex-shrink-0 bn-digit" style="font-size: 11px;">{{ $zoneWidgets->count() }}</span>
                    </div>

                    <div class="card-body p-3 d-flex flex-column gap-2 flex-grow-1">
                        @forelse($zoneWidgets as $w)
                            <div class="widget-item-card position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <div class="text-truncate min-w-0">
                                        <span class="badge bg-light text-primary border mb-1 text-uppercase px-2 py-1 fw-semibold" style="font-size: 9.5px;">{{ str_replace('_', ' ', $w->widget_type) }}</span>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 13px;" title="{{ $w->title }}">{{ $w->title }}</h6>
                                    </div>
                                    <div class="d-inline-flex gap-1 flex-shrink-0 widget-action-tools">
                                        {{-- Edit Button --}}
                                        <button type="button" class="btn-icon-sq text-primary" 
                                            onclick='openEditModal(@json($w))' title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        {{-- Delete Button (Opens Modal) --}}
                                        <button type="button" class="btn-icon-sq text-danger" 
                                            onclick="openDeleteWidgetModal('{{ route('admin.content.widgets.destroy', $w->id) }}', '{{ addslashes($w->title) }}')" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                @if(!empty($w->content))
                                    <div class="widget-code-preview text-truncate font-monospace mb-2">
                                        {{ Str::limit(strip_tags($w->content), 70) }}
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                    <span class="small text-muted" style="font-size: 11px;">Order: <strong class="text-dark font-monospace bn-digit">{{ $w->order_index }}</strong></span>
                                    <form action="{{ route('admin.content.widgets.toggle', $w->id) }}" method="POST" class="d-inline m-0">
                                        @csrf
                                        <button type="submit" class="badge btn-status-toggle {{ $w->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $w->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 my-auto text-muted small">
                                <i class="fas fa-cubes fa-2x opacity-25 d-block mb-2"></i>
                                <span style="font-size: 12px;">No widgets placed in this zone yet.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- ================= ADD WIDGET MODAL ================= -->
<div class="modal fade" id="addWidgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('admin.content.widgets.store') }}" method="POST" class="modal-content shadow-lg">
            @csrf
            <div class="modal-header border-bottom py-2 px-3 bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 14px;">Add New Widget</h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label">Widget Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control fw-semibold" placeholder=" Popular Categories, Promo Banner" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Display Zone <span class="text-danger">*</span></label>
                        <select name="widget_zone" class="form-select" required>
                            <option value="sidebar-right">Right Sidebar</option>
                            <option value="sidebar-left">Left Sidebar</option>
                            <option value="footer-widgets">Footer Area</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Widget Type <span class="text-danger">*</span></label>
                        <select name="widget_type" class="form-select" required onchange="toggleContentField(this.value, 'add')">
                            <option value="search">Search Form</option>
                            <option value="categories">Categories List</option>
                            <option value="recent_posts">Recent Blog Articles</option>
                            <option value="custom_html">Custom HTML / Ad Banner</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3" id="add-content-wrapper" style="display: none;">
                    <label class="form-label">HTML Content / Code</label>
                    <textarea name="content" class="form-control font-monospace" rows="4" placeholder="<div>Your custom html/script</div>"></textarea>
                </div>
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Sorting Order</label>
                        <input type="number" name="order_index" class="form-control font-monospace bn-digit" value="0">
                    </div>
                    <div class="col-12 col-sm-6 d-flex align-items-center pt-2 pt-sm-4">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="add_is_active" value="1" checked style="cursor: pointer;">
                            <label class="form-check-label fw-semibold small text-dark" for="add_is_active" style="font-size: 12px; cursor: pointer;">Enable Widget</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact shadow-xs">Save Widget</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT WIDGET MODAL ================= -->
<div class="modal fade" id="editWidgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="editWidgetForm" method="POST" class="modal-content shadow-lg">
            @csrf
            <div class="modal-header border-bottom py-2 px-3 bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 14px;">Edit Widget</h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label">Widget Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="edit-title" class="form-control fw-semibold" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Display Zone <span class="text-danger">*</span></label>
                        <select name="widget_zone" id="edit-zone" class="form-select" required>
                            <option value="sidebar-right">Right Sidebar</option>
                            <option value="sidebar-left">Left Sidebar</option>
                            <option value="footer-widgets">Footer Area</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Widget Type <span class="text-danger">*</span></label>
                        <select name="widget_type" id="edit-type" class="form-select" required onchange="toggleContentField(this.value, 'edit')">
                            <option value="search">Search Form</option>
                            <option value="categories">Categories List</option>
                            <option value="recent_posts">Recent Blog Articles</option>
                            <option value="custom_html">Custom HTML / Ad Banner</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3" id="edit-content-wrapper" style="display: none;">
                    <label class="form-label">HTML Content / Code</label>
                    <textarea name="content" id="edit-content" class="form-control font-monospace" rows="4"></textarea>
                </div>
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Sorting Order</label>
                        <input type="number" name="order_index" id="edit-order" class="form-control font-monospace bn-digit">
                    </div>
                    <div class="col-12 col-sm-6 d-flex align-items-center pt-2 pt-sm-4">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit-is-active" value="1" style="cursor: pointer;">
                            <label class="form-check-label fw-semibold small text-dark" for="edit-is-active" style="font-size: 12px; cursor: pointer;">Enable Widget</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-compact shadow-xs">Update Widget</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= DELETE CONFIRMATION MODAL (Replaces confirm()) ================= -->
<div class="modal fade" id="deleteWidgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-alt fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Widget?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to delete <strong id="deleteWidgetName" class="text-dark"></strong>?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteWidgetForm" method="POST" action="" class="m-0">
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
    function toggleContentField(type, formMode) {
        const wrapper = document.getElementById(formMode + '-content-wrapper');
        wrapper.style.display = (type === 'custom_html') ? 'block' : 'none';
    }

    function openEditModal(widget) {
        document.getElementById('editWidgetForm').action = "{{ url('admin/content/widgets') }}/" + widget.id + "/update";
        document.getElementById('edit-title').value = widget.title;
        document.getElementById('edit-zone').value = widget.widget_zone;
        document.getElementById('edit-type').value = widget.widget_type;
        document.getElementById('edit-content').value = widget.content || '';
        document.getElementById('edit-order').value = widget.order_index;
        document.getElementById('edit-is-active').checked = widget.is_active == 1;

        toggleContentField(widget.widget_type, 'edit');

        var editModal = new bootstrap.Modal(document.getElementById('editWidgetModal'));
        editModal.show();
    }

    function openDeleteWidgetModal(actionUrl, widgetTitle) {
        document.getElementById('deleteWidgetForm').action = actionUrl;
        document.getElementById('deleteWidgetName').innerText = '"' + widgetTitle + '"';
        var delModal = new bootstrap.Modal(document.getElementById('deleteWidgetModal'));
        delModal.show();
    }
</script>
@endsection