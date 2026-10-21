@extends('layouts.admin')

@section('title', 'SITCOMMERCE Block Builder - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #blockBuilderRoot .btn,
    #blockBuilderRoot .btn-sm,
    #blockBuilderRoot button,
    #blockBuilderRoot .form-control,
    #blockBuilderRoot .form-select,
    #blockBuilderRoot .badge,
    #blockBuilderRoot .card,
    #blockBuilderRoot .builder-header-card,
    #blockBuilderRoot .table-container-card,
    #blockBuilderRoot .shortcode-pill,
    #blockBuilderRoot .section-item-mobile,
    #blockBuilderRoot .action-btn-circle,
    #blockBuilderRoot .collapse-toggle-btn,
    .builder-modal-scope .modal-content,
    .builder-modal-scope .btn,
    .builder-modal-scope .form-control,
    .builder-modal-scope .badge {
        border-radius: 5px !important;
    }

    #blockBuilderRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #blockBuilderRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Bengali Digits & Monospace Formatting */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Header */
    .builder-header-card {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 14px;
    }

    /* Enterprise Container Cards */
    .table-container-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .table-card-header {
        padding: 11px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Shortcode Guide Pill */
    .shortcode-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 6px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .shortcode-pill:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .shortcode-pill code {
        color: #2563eb;
        font-size: 11px;
        font-weight: 600;
        user-select: all;
    }
    .btn-copy-code {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .btn-copy-code:hover {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .builder-table {
        width: 100%;
        margin-bottom: 0;
    }
    .builder-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .builder-table tbody td {
        padding: 7px 6px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .builder-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .builder-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .builder-table th,
        .builder-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 6 Columns Proportional Distribution Summing to Exactly 100% */
        .col-sec-order   { width: 6% !important; }
        .col-sec-title   { width: 26% !important; }
        .col-sec-btn     { width: 18% !important; }
        .col-sec-content { width: 28% !important; }
        .col-sec-status  { width: 10% !important; }
        .col-sec-actions { width: 12% !important; }
    }

    /* Status Badges */
    .status-badge-active {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .status-badge-disabled {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Action Buttons */
    .action-btn-circle {
        width: 26px !important;
        height: 26px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .action-btn-circle.edit {
        background: #eff6ff !important;
        color: #2563eb !important;
        border-color: #bfdbfe !important;
    }
    .action-btn-circle.edit:hover {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .action-btn-circle.delete {
        background: #fef2f2 !important;
        color: #dc2626 !important;
        border-color: #fecaca !important;
    }
    .action-btn-circle.delete:hover {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #blockBuilderRoot .form-control,
    #blockBuilderRoot .form-select,
    .builder-modal-scope .form-control,
    .builder-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #blockBuilderRoot .form-control:focus,
    #blockBuilderRoot .form-select:focus,
    .builder-modal-scope .form-control:focus,
    .builder-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    textarea.form-control {
        min-height: 80px;
    }

    /* Enhanced Toggle Switches */
    .form-switch .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
        border-color: #cbd5e1;
    }
    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .builder-header-card { padding: 12px 14px; }
        .table-card-header { padding: 10px 12px; }
        .section-item-mobile {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            margin-bottom: 8px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .section-item-mobile-actions {
            display: flex;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
        }
        .section-item-mobile-actions button {
            flex: 1;
            padding: 6px 10px;
            font-size: 11.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="blockBuilderRoot">

    <!-- 1. Top Header Card -->
    <div class="builder-header-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">
                    <i class="fas fa-cubes me-1"></i> BLOCK ENGINE
                </span>
                <span class="badge bg-light text-white-50 border border-secondary font-monospace" style="font-size: 9px;">
                    STOREFRONT LAYOUT
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-cubes text-primary"></i>
                <span>SITCOMMERCE Block Builder</span>
            </h5>
            <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                Design, order and manage custom storefront content blocks, categories &amp; shortcode layouts.
            </small>
        </div>

        <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addSectionModal">
            <i class="fas fa-plus me-1"></i> <span>Add Section Block</span>
        </button>
    </div>

    <!-- 2. Shortcodes Library Guide (COLLAPSIBLE) -->
    <div class="table-container-card mb-3">
        <div class="table-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-code text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">SitCommerce Shortcodes Library</h6>
            </div>
            <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseShortcodeGuide" aria-expanded="false" aria-controls="collapseShortcodeGuide" title="Toggle Shortcode Library">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse" id="collapseShortcodeGuide">
            <div class="p-3 bg-light bg-opacity-50">
                <div class="row g-2">
                    @php
                        $shortcodeList = [
                            '[products limit="8" columns="4"]' => 'Standard product grid',
                            '[products category="fashion" limit="8" columns="4"]' => 'Products by category',
                            '[products category="fashion,bags" category_operator="IN"]' => 'Multi-category products',
                            '[products ids="1,2,3" columns="3"]' => 'Specific product IDs',
                            '[products skus="ABC123,XYZ456"]' => 'Products by SKU code',
                            '[products orderby="rand" limit="8" columns="4"]' => 'Random product grid',
                            '[products orderby="price" order="ASC" limit="8"]' => 'Price low to high',
                            '[products orderby="popularity" limit="8"]' => 'Best selling products',
                            '[products on_sale="true" limit="8" columns="4"]' => 'Discount / Sale products',
                            '[products visibility="featured" limit="8" columns="4"]' => 'Featured products',
                            '[products min_price="500" max_price="2000"]' => 'Price range filtering',
                            '[products stock_status="instock" limit="8"]' => 'In-stock only',
                            '[products limit="12" columns="4" paginate="true"]' => 'Pure AJAX paginated products',
                            '[featured_products limit="8" columns="4"]' => 'Featured showcase',
                            '[sale_products limit="8" columns="4"]' => 'Sale items showcase',
                            '[recent_products limit="8" columns="4"]' => 'Recent items showcase',
                            '[best_selling_products limit="8" columns="4"]' => 'Best seller items',
                            '[top_rated_products limit="8" columns="4"]' => 'Top rated items',
                            '[product_categories number="6"]' => 'Categories circle grid',
                            '[product id="12"]' => 'Single product card'
                        ];
                    @endphp
                    @foreach($shortcodeList as $code => $desc)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="shortcode-pill shadow-xs">
                                <div class="text-truncate">
                                    <code>{{ $code }}</code>
                                    <small class="text-muted d-block" style="font-size: 10px;">{{ $desc }}</small>
                                </div>
                                <button type="button" class="btn-copy-code shadow-xs" onclick="copyShortcode('{{ $code }}')" title="Copy Shortcode">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Active Sections Table Card (COLLAPSIBLE) -->
    <div class="table-container-card">
        <div class="table-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-layer-group text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Active Storefront Section Blocks</h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-0.5" style="font-size: 9.5px;">
                    {{ count($sections) }} {{ Str::plural('Block', count($sections)) }}
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseSectionsTable" aria-expanded="true" aria-controls="collapseSectionsTable" title="Toggle Section">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseSectionsTable">
            <!-- Desktop Table View (100% Fit Screen with Zero Horizontal Scroll) -->
            <div class="table-responsive pc-fit-container d-none d-md-block">
                <table class="table builder-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-sec-order text-center">Order</th>
                            <th class="col-sec-title">Icon &amp; Title</th>
                            <th class="col-sec-btn">Header Button</th>
                            <th class="col-sec-content">Shortcode / Content</th>
                            <th class="col-sec-status text-center">Status</th>
                            <th class="text-end pe-3 col-sec-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sections as $sec)
                            <tr>
                                <!-- Order Index -->
                                <td class="ps-3 col-sec-order text-center fw-bold font-monospace text-muted bn-digit">
                                    {{ $sec->order_index }}
                                </td>

                                <!-- Icon & Title -->
                                <td class="col-sec-title">
                                    <div class="d-flex align-items-center gap-1.5 min-w-0">
                                        @if(!empty($sec->icon))
                                            <i class="{{ $sec->icon }}" style="color: {{ $sec->title_color ?? '#2563eb' }}; font-size: 13px;"></i>
                                        @endif
                                        <div class="min-w-0 flex-grow-1">
                                            <strong class="text-dark d-block text-truncate" style="font-size: 11.5px; line-height: 1.3;" title="{{ $sec->title ?: '(Content Only)' }}">
                                                {{ $sec->title ?: '(Content Only)' }}
                                            </strong>
                                            @if(isset($sec->show_header) && (int)$sec->show_header === 0)
                                                <span class="badge bg-warning-subtle text-warning-emphasis font-monospace px-1 py-0" style="font-size: 8.5px;">Header Off</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Button Tag -->
                                <td class="col-sec-btn">
                                    @if(!empty($sec->btn_text) || !empty($sec->btn_url))
                                        <span class="badge px-2 py-0.5 border text-truncate d-inline-block font-monospace" style="background-color: {{ $sec->btn_bg ?? '#eff6ff' }}; color: {{ $sec->btn_color ?? '#2563eb' }}; font-size: 10px; max-width: 140px;">
                                            {{ $sec->btn_text ?: 'Link' }} <i class="fas fa-external-link-alt ms-1" style="font-size: 7.5px;"></i>
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>

                                <!-- Content Strip -->
                                <td class="col-sec-content">
                                    <span class="text-secondary font-monospace d-inline-block text-truncate" style="font-size: 10.5px; max-width: 240px;" title="{{ strip_tags($sec->custom_content) }}">
                                        {{ Str::limit(strip_tags($sec->custom_content), 50) }}
                                    </span>
                                </td>

                                <!-- Status Badge -->
                                <td class="col-sec-status text-center">
                                    @if($sec->is_active)
                                        <span class="status-badge-active"><i class="fas fa-circle" style="font-size: 5px;"></i> Active</span>
                                    @else
                                        <span class="status-badge-disabled"><i class="fas fa-circle" style="font-size: 5px;"></i> Off</span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-end pe-3 col-sec-actions">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="action-btn-circle edit shadow-xs" data-bs-toggle="modal" data-bs-target="#editModal{{ $sec->id }}" title="Edit Block">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="action-btn-circle delete shadow-xs" onclick="openDeleteConfirmationModal('{{ route('admin.theme.setting.home_builder.destroy', $sec->id) }}', '{{ addslashes($sec->title ?? 'Section') }}')" title="Delete Block">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted small">
                                    <i class="fas fa-cubes fa-2x opacity-25 mb-2 d-block"></i>
                                    <span class="fw-semibold">No custom sections added yet. Click "Add Section Block" to begin.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (100/100 Touch-Comfort) -->
            <div class="d-block d-md-none p-2">
                @forelse($sections as $sec)
                    <div class="section-item-mobile shadow-xs">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark border font-monospace bn-digit" style="font-size: 9.5px;">Order: {{ $sec->order_index }}</span>
                            @if($sec->is_active)
                                <span class="status-badge-active"><i class="fas fa-circle" style="font-size: 5px;"></i> Active</span>
                            @else
                                <span class="status-badge-disabled"><i class="fas fa-circle" style="font-size: 5px;"></i> Off</span>
                            @endif
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                @if(!empty($sec->icon))
                                    <i class="{{ $sec->icon }}" style="color: {{ $sec->title_color ?? '#2563eb' }}; font-size: 12px;"></i>
                                @endif
                                <strong class="text-dark text-truncate" style="font-size: 12.5px;">{{ $sec->title ?: '(Content Only)' }}</strong>
                                @if(isset($sec->show_header) && (int)$sec->show_header === 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis font-monospace px-1 py-0" style="font-size: 8px;">Header Off</span>
                                @endif
                            </div>
                            <div class="text-muted font-monospace text-truncate mt-1" style="font-size: 10.5px;">
                                {{ Str::limit(strip_tags($sec->custom_content), 40) }}
                            </div>
                        </div>
                        <div class="section-item-mobile-actions">
                            <button type="button" class="btn btn-light border btn-compact" data-bs-toggle="modal" data-bs-target="#editModal{{ $sec->id }}">
                                <i class="fas fa-pen text-primary"></i> <span>Edit</span>
                            </button>
                            <button type="button" class="btn btn-light border btn-compact text-danger" onclick="openDeleteConfirmationModal('{{ route('admin.theme.setting.home_builder.destroy', $sec->id) }}', '{{ addslashes($sec->title ?? 'Section') }}')">
                                <i class="fas fa-trash-alt"></i> <span>Delete</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-cubes fa-2x opacity-25 mb-2 d-block"></i>
                        No custom sections found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: ADD SECTION BLOCK ================= -->
<div class="modal fade builder-modal-scope" id="addSectionModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.theme.setting.home_builder.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                        <i class="fas fa-plus-circle text-primary"></i> <span>Add Section Block</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-3">
                    <!-- Title & Header Bar Toggle -->
                    <div class="row g-2 mb-2">
                        <div class="col-8 col-md-7">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. New Arrivals">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label">Title Color</label>
                            <input type="color" name="title_color" class="form-control form-control-color w-100" value="#0f172a" style="height: 33px;">
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-center pt-md-3">
                            <div class="p-2 border bg-light w-100 d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                <label class="form-check-label fw-bold small m-0" for="addShowHeader" style="font-size: 11px;">Header Bar</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="show_header" value="1" id="addShowHeader" checked>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon & Order -->
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">FontAwesome Icon Class</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-icons"></i></span>
                                <input type="text" name="icon" class="form-control font-monospace" placeholder="fas fa-layer-group" value="fas fa-layer-group">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Display Order Index</label>
                            <input type="number" name="order_index" class="form-control font-monospace bn-digit" value="10">
                        </div>
                    </div>

                    <!-- Action Button Box (Symmetric Grid) -->
                    <div class="card p-2.5 bg-light border-0 mb-2" style="border-radius: 5px;">
                        <span class="fw-bold text-dark small mb-1 d-block" style="font-size: 11.5px;">
                            <i class="fas fa-link text-primary me-1"></i> Header Action Link Button
                        </span>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Button Text</label>
                                <input type="text" name="btn_text" class="form-control" placeholder="e.g. View All">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Target URL</label>
                                <input type="text" name="btn_url" class="form-control font-monospace" placeholder="/shop">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Background Color</label>
                                <input type="color" name="btn_bg" class="form-control form-control-color w-100" value="#ffffff" style="height: 30px;">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Text Color</label>
                                <input type="color" name="btn_color" class="form-control form-control-color w-100" value="#2563eb" style="height: 30px;">
                            </div>
                        </div>
                    </div>

                    <!-- Shortcode Input Area -->
                    <div class="mb-2">
                        <label class="form-label">Shortcode / Content Block *</label>
                        <textarea name="custom_content" rows="3" class="form-control font-monospace" placeholder='[products limit="8" columns="4"]' required></textarea>
                    </div>

                    <div class="p-2 border bg-light d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                        <label class="form-check-label fw-bold small m-0" for="newActiveSwitch">Activate Section Block on Storefront</label>
                        <div class="form-check form-switch m-0 ps-0">
                            <input class="form-check-input ms-0" type="checkbox" name="is_active" value="1" id="newActiveSwitch" checked>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">Save Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODALS: EDIT SECTION BLOCKS ================= -->
@foreach($sections as $sec)
<div class="modal fade builder-modal-scope" id="editModal{{ $sec->id }}" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.theme.setting.home_builder.update', $sec->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                        <i class="fas fa-pen text-primary"></i> <span>Edit Section: {{ $sec->title ?: 'Block #' . $sec->id }}</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-3">
                    <!-- Title & Header Bar Toggle -->
                    <div class="row g-2 mb-2">
                        <div class="col-8 col-md-7">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $sec->title }}">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label">Title Color</label>
                            <input type="color" name="title_color" class="form-control form-control-color w-100" value="{{ $sec->title_color ?? '#0f172a' }}" style="height: 33px;">
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-center pt-md-3">
                            <div class="p-2 border bg-light w-100 d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                                <label class="form-check-label fw-bold small m-0" for="editShowHeader{{ $sec->id }}" style="font-size: 11px;">Header Bar</label>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="show_header" value="1" id="editShowHeader{{ $sec->id }}" {{ (!isset($sec->show_header) || (int)$sec->show_header === 1) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon & Order -->
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">FontAwesome Icon Class</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-icons"></i></span>
                                <input type="text" name="icon" class="form-control font-monospace" value="{{ $sec->icon ?? 'fas fa-layer-group' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Display Order Index</label>
                            <input type="number" name="order_index" class="form-control font-monospace bn-digit" value="{{ $sec->order_index }}">
                        </div>
                    </div>

                    <!-- Action Button Box (Symmetric Grid) -->
                    <div class="card p-2.5 bg-light border-0 mb-2" style="border-radius: 5px;">
                        <span class="fw-bold text-dark small mb-1 d-block" style="font-size: 11.5px;">
                            <i class="fas fa-link text-primary me-1"></i> Header Action Link Button
                        </span>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Button Text</label>
                                <input type="text" name="btn_text" class="form-control" value="{{ $sec->btn_text }}" placeholder="Text">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Target URL</label>
                                <input type="text" name="btn_url" class="form-control font-monospace" value="{{ $sec->btn_url }}" placeholder="URL">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Background Color</label>
                                <input type="color" name="btn_bg" class="form-control form-control-color w-100" value="{{ $sec->btn_bg ?? '#ffffff' }}" style="height: 30px;">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted" style="font-size: 10.5px;">Text Color</label>
                                <input type="color" name="btn_color" class="form-control form-control-color w-100" value="{{ $sec->btn_color ?? '#2563eb' }}" style="height: 30px;">
                            </div>
                        </div>
                    </div>

                    <!-- Shortcode Input Area -->
                    <div class="mb-2">
                        <label class="form-label">Shortcode / Content Block *</label>
                        <textarea name="custom_content" rows="3" class="form-control font-monospace" required>{{ $sec->custom_content }}</textarea>
                    </div>

                    <div class="p-2 border bg-light d-flex align-items-center justify-content-between" style="border-radius: 5px;">
                        <label class="form-check-label fw-bold small m-0" for="act{{ $sec->id }}">Active Section on Storefront</label>
                        <div class="form-check form-switch m-0 ps-0">
                            <input class="form-check-input ms-0" type="checkbox" name="is_active" value="1" id="act{{ $sec->id }}" {{ $sec->is_active ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">Update Block</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- ================= DELETE CONFIRMATION MODAL ================= -->
<div class="modal fade builder-modal-scope" id="deleteConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Section Block?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to remove <strong id="deleteSectionTitle" class="text-dark">this section</strong>?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <form id="confirmedDeleteForm" action="" method="POST" class="d-inline m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= ACTION SUCCESS NOTIFICATION MODAL ================= -->
<div class="modal fade builder-modal-scope" id="actionNotificationModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-success">
                    <i class="fas fa-circle-check fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="notificationMessageText" style="font-size: 12px; line-height: 1.45;">{{ session('success') }}</p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- Copy Toast Component -->
<div id="sc-copy-toast" style="position: fixed; bottom: 20px; right: 20px; background: #0f172a; color: #ffffff; padding: 7px 14px; border-radius: 5px; font-size: 11.5px; font-weight: 600; z-index: 109999; display: none; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-check-circle text-success"></i>
    <span>Shortcode copied to clipboard!</span>
</div>

<script>
    function copyShortcode(code) {
        navigator.clipboard.writeText(code).then(() => {
            const toast = document.getElementById('sc-copy-toast');
            if (toast) {
                toast.style.display = 'inline-flex';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 2000);
            }
        });
    }

    function openDeleteConfirmationModal(actionUrl, sectionTitle) {
        const form = document.getElementById('confirmedDeleteForm');
        const titleElem = document.getElementById('deleteSectionTitle');
        if (form && titleElem) {
            form.action = actionUrl;
            titleElem.textContent = sectionTitle;
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            const notifModal = new bootstrap.Modal(document.getElementById('actionNotificationModal'));
            notifModal.show();
        @endif
    });
</script>
@endsection