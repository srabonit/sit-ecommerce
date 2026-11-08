
@extends('layouts.admin')

@section('title', 'Block Builder - ' . config('app.name'))

@section('admin_content')
<style>
    #blockBuilderRoot {
        --bb-primary: #2563eb;
        --bb-primary-soft: #eff6ff;
        --bb-dark: #0f172a;
        --bb-text: #334155;
        --bb-muted: #64748b;
        --bb-border: #e2e8f0;
        --bb-light: #f8fafc;
        --bb-radius: 5px;
        color: var(--bb-text);
    }

    /* Global Radius */
    #blockBuilderRoot *,
    .builder-modal-scope *,
    #sc-copy-toast {
        border-radius: 5px;
    }

    #blockBuilderRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #blockBuilderRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #blockBuilderRoot .font-monospace,
    #blockBuilderRoot .bn-digit {
        font-weight: 700;
    }

    #blockBuilderRoot .btn-compact {
        min-height: 30px;
        padding: 5px 12px;
        font-size: 11.5px;
        line-height: 1.3;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .2s ease;
    }

    /* Header */
    #blockBuilderRoot .builder-header-card {
        background: linear-gradient(120deg, #0f172a, #1e293b);
        color: #fff;
        padding: 17px 20px;
        border: 1px solid #263449;
        box-shadow: 0 2px 8px rgba(15,23,42,.08);
        margin-bottom: 14px;
    }

    #blockBuilderRoot .builder-header-card h5 {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.3px;
    }

    #blockBuilderRoot .header-description {
        color: #aab8ca;
        font-size: 11.5px;
        margin-top: 5px;
        line-height: 1.5;
    }

    /* Main Cards */
    #blockBuilderRoot .table-container-card {
        background: #fff;
        border: 1px solid var(--bb-border);
        box-shadow: 0 2px 6px rgba(15,23,42,.035);
        margin-bottom: 14px;
        overflow: hidden;
    }

    #blockBuilderRoot .table-card-header {
        min-height: 45px;
        padding: 10px 15px;
        background: #fff;
        border-bottom: 1px solid var(--bb-border);
    }

    #blockBuilderRoot .card-heading {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--bb-dark);
        margin: 0;
    }

    #blockBuilderRoot .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #64748b;
        transition: all .2s ease;
    }

    #blockBuilderRoot .collapse-toggle-btn:hover {
        background: var(--bb-primary);
        border-color: var(--bb-primary);
        color: #fff;
    }

    #blockBuilderRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #blockBuilderRoot .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    /* Shortcode Library */
    #blockBuilderRoot .shortcode-pill {
        min-width: 0;
        min-height: 54px;
        background: #fff;
        border: 1px solid var(--bb-border);
        padding: 7px 9px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: all .2s ease;
    }

    #blockBuilderRoot .shortcode-pill:hover {
        border-color: #93c5fd;
        background: #f8fbff;
        box-shadow: 0 2px 7px rgba(37,99,235,.06);
    }

    #blockBuilderRoot .shortcode-pill code {
        color: var(--bb-primary);
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    #blockBuilderRoot .shortcode-pill small {
        color: var(--bb-muted);
        font-size: 10px;
        margin-top: 3px;
    }

    #blockBuilderRoot .btn-copy-code {
        width: 27px;
        height: 27px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border: 1px solid #dbe3ed;
        color: #475569;
        font-size: 10px;
        cursor: pointer;
        transition: all .2s ease;
    }

    #blockBuilderRoot .btn-copy-code:hover {
        background: var(--bb-primary);
        color: #fff;
        border-color: var(--bb-primary);
    }

    /* Table */
    #blockBuilderRoot .builder-table {
        width: 100%;
        margin: 0;
        table-layout: fixed;
    }

    #blockBuilderRoot .builder-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        padding: 10px 7px;
        border-bottom: 1px solid var(--bb-border);
        white-space: nowrap;
    }

    #blockBuilderRoot .builder-table tbody td {
        padding: 9px 7px;
        vertical-align: middle;
        font-size: 11px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    #blockBuilderRoot .builder-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #blockBuilderRoot .builder-table tbody tr:hover {
        background: #fafcff;
    }

    #blockBuilderRoot .builder-table th,
    #blockBuilderRoot .builder-table td {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #blockBuilderRoot .col-sec-order { width: 6%; }
    #blockBuilderRoot .col-sec-title { width: 26%; }
    #blockBuilderRoot .col-sec-btn { width: 18%; }
    #blockBuilderRoot .col-sec-content { width: 28%; }
    #blockBuilderRoot .col-sec-status { width: 10%; }
    #blockBuilderRoot .col-sec-actions { width: 12%; }

    /* Status */
    #blockBuilderRoot .status-badge-active,
    #blockBuilderRoot .status-badge-disabled {
        padding: 3px 8px;
        font-size: 9.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
    }

    #blockBuilderRoot .status-badge-active {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }

    #blockBuilderRoot .status-badge-disabled {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }

    /* Actions */
    #blockBuilderRoot .action-btn-circle {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10.5px;
        border: 1px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: all .2s ease;
    }

    #blockBuilderRoot .action-btn-circle.edit {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    #blockBuilderRoot .action-btn-circle.edit:hover {
        background: #2563eb;
        color: #fff;
    }

    #blockBuilderRoot .action-btn-circle.delete {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    #blockBuilderRoot .action-btn-circle.delete:hover {
        background: #dc2626;
        color: #fff;
    }

    /* Forms */
    #blockBuilderRoot .form-label,
    .builder-modal-scope .form-label {
        display: block;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    #blockBuilderRoot .form-control,
    #blockBuilderRoot .form-select,
    .builder-modal-scope .form-control,
    .builder-modal-scope .form-select {
        min-height: 34px;
        padding: 6px 10px;
        font-size: 12px;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    #blockBuilderRoot .form-control:focus,
    #blockBuilderRoot .form-select:focus,
    .builder-modal-scope .form-control:focus,
    .builder-modal-scope .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(37,99,235,.10);
    }

    #blockBuilderRoot textarea.form-control,
    .builder-modal-scope textarea.form-control {
        min-height: 85px;
        resize: vertical;
    }

    #blockBuilderRoot .form-control-color,
    .builder-modal-scope .form-control-color {
        padding: 3px;
        min-height: 34px;
    }

    #blockBuilderRoot .form-switch .form-check-input,
    .builder-modal-scope .form-switch .form-check-input {
        width: 36px;
        height: 19px;
        margin-top: 0;
        cursor: pointer;
        border-color: #cbd5e1;
    }

    #blockBuilderRoot .form-switch .form-check-input:checked,
    .builder-modal-scope .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Modal */
    .builder-modal-scope {
        z-index: 106000;
    }

    .builder-modal-scope .modal-dialog {
        margin: 1rem auto;
    }

    .builder-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(15,23,42,.16);
    }

    .builder-modal-scope .modal-header {
        min-height: 48px;
    }

    .builder-modal-scope .modal-title {
        font-size: 13px;
        font-weight: 700;
    }

    .builder-modal-scope .modal-body {
        max-height: calc(100vh - 170px);
        overflow-y: auto;
    }

    .builder-modal-scope .modal-footer {
        border-top: 1px solid #e2e8f0;
    }

    .builder-modal-scope .form-check-label {
        font-size: 11px;
        font-weight: 600;
        color: #334155;
    }

    .builder-modal-scope .section-setting-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 9px 11px;
    }

    .builder-modal-scope .setting-title {
        display: block;
        color: #1e293b;
        font-size: 11.5px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    /* Mobile Cards */
    #blockBuilderRoot .section-item-mobile {
        background: #fff;
        border: 1px solid var(--bb-border);
        padding: 11px;
        margin-bottom: 9px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(15,23,42,.035);
    }

    #blockBuilderRoot .section-item-mobile:last-child {
        margin-bottom: 0;
    }

    #blockBuilderRoot .section-item-mobile-actions {
        display: flex;
        gap: 8px;
        padding-top: 9px;
        border-top: 1px dashed #e2e8f0;
    }

    #blockBuilderRoot .section-item-mobile-actions button {
        flex: 1;
        min-height: 32px;
    }

    #blockBuilderRoot .mobile-section-title {
        min-width: 0;
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
    }

    /* Empty State */
    #blockBuilderRoot .empty-state {
        padding: 35px 15px;
        text-align: center;
        color: #94a3b8;
    }

    #blockBuilderRoot .empty-state i {
        font-size: 27px;
        opacity: .45;
        margin-bottom: 10px;
    }

    #blockBuilderRoot .empty-state p {
        font-size: 11.5px;
        color: #64748b;
        margin: 0;
    }

    /* Toast */
    #sc-copy-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #0f172a;
        color: #fff;
        padding: 9px 14px;
        font-size: 11.5px;
        font-weight: 600;
        z-index: 109999;
        display: none;
        align-items: center;
        gap: 7px;
        box-shadow: 0 5px 18px rgba(0,0,0,.18);
    }

    @media (max-width: 991.98px) {
        #blockBuilderRoot .builder-table {
            table-layout: auto;
        }

        #blockBuilderRoot .builder-table th,
        #blockBuilderRoot .builder-table td {
            padding-left: 6px;
            padding-right: 6px;
        }
    }

    @media (max-width: 767.98px) {
        #blockBuilderRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #blockBuilderRoot .builder-header-card {
            padding: 14px;
        }

        #blockBuilderRoot .builder-header-card h5 {
            font-size: 16px;
        }

        #blockBuilderRoot .header-description {
            font-size: 10.5px;
        }

        #blockBuilderRoot .table-card-header {
            padding: 10px 11px;
        }

        #blockBuilderRoot .card-heading {
            font-size: 11.5px;
        }

        #blockBuilderRoot .shortcode-pill {
            min-height: 52px;
        }

        .builder-modal-scope .modal-dialog {
            margin: 10px;
        }

        .builder-modal-scope .modal-body {
            padding: 12px !important;
        }

        #sc-copy-toast {
            right: 12px;
            bottom: 12px;
        }
    }

    @media (max-width: 420px) {
        #blockBuilderRoot .builder-header-card .btn-compact {
            width: 100%;
        }

        .builder-modal-scope .modal-dialog {
            margin: 7px;
        }

        .builder-modal-scope .modal-body {
            max-height: calc(100vh - 145px);
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="blockBuilderRoot">

    <!-- Header -->
 <div class="builder-header-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3 p-3 border rounded-3 shadow-xs" style="background: #ffffff !important;">
    <div class="min-w-0">
        <h5 class="fw-bold mb-1 d-flex align-items-center gap-2 text-nowrap" style="font-size: 16px; color: #1e293b !important;">
            <i class="fas fa-cubes text-primary"></i>
            <span style="color: #1e293b !important;">Custom Block Builder</span>
        </h5>
        <small class="d-block mt-0 text-nowrap" style="font-size: 11.5px; line-height: 1.35; color: #64748b !important;">
            Manage storefront sections and shortcodes.
        </small>
    </div>

    <button type="button"
        class="btn btn-primary btn-compact shadow-xs fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap flex-shrink-0"
        style="height: 34px; font-size: 11.5px;"
        data-bs-toggle="modal"
        data-bs-target="#addSectionModal">
        <i class="fas fa-plus"></i>
        <span>Add Section</span>
    </button>
</div>
    <!-- Shortcode Library -->
    <div class="table-container-card">
        <div class="table-card-header d-flex justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <i class="fas fa-code text-primary"></i>
                <h6 class="card-heading">Product Shortcodes</h6>
            </div>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm flex-shrink-0"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseShortcodeGuide"
                    aria-expanded="false"
                    aria-controls="collapseShortcodeGuide"
                    title="Toggle Library">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse" id="collapseShortcodeGuide">
            <div class="p-3 bg-light bg-opacity-50">
                <div class="row g-2">
                    @php
                        $shortcodeList = [
                            '[products limit="8" columns="4"]' => 'Standard product grid',
                            '[products category="fashion" limit="8" columns="4"]' => 'Category products',
                            '[products category="fashion,bags" category_operator="IN"]' => 'Multiple categories',
                            '[products ids="1,2,3" columns="3"]' => 'Selected product IDs',
                            '[products skus="ABC123,XYZ456"]' => 'Products by SKU',
                            '[products orderby="rand" limit="8" columns="4"]' => 'Random products',
                            '[products orderby="price" order="ASC" limit="8"]' => 'Lowest price first',
                            '[products orderby="popularity" limit="8"]' => 'Best sellers',
                            '[products on_sale="true" limit="8" columns="4"]' => 'Sale products',
                            '[products visibility="featured" limit="8" columns="4"]' => 'Featured products',
                            '[products min_price="500" max_price="2000"]' => 'Price range',
                            '[products stock_status="instock" limit="8"]' => 'In-stock products',
                            '[products limit="12" columns="4" paginate="true"]' => 'AJAX pagination',
                            '[featured_products limit="8" columns="4"]' => 'Featured showcase',
                            '[sale_products limit="8" columns="4"]' => 'Sale showcase',
                            '[recent_products limit="8" columns="4"]' => 'Recent products',
                            '[best_selling_products limit="8" columns="4"]' => 'Best sellers',
                            '[top_rated_products limit="8" columns="4"]' => 'Top rated',
                            '[product_categories number="6"]' => 'Category grid',
                            '[product id="12"]' => 'Single product'
                        ];
                    @endphp

                    @foreach($shortcodeList as $code => $desc)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="shortcode-pill">
                                <div class="text-truncate flex-grow-1 min-w-0">
                                    <code>{{ $code }}</code>
                                    <small class="d-block">{{ $desc }}</small>
                                </div>

                                <button type="button"
                                        class="btn-copy-code"
                                        onclick="copyShortcode('{{ $code }}')"
                                        title="Copy">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Sections -->
    <div class="table-container-card">
        <div class="table-card-header d-flex justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <i class="fas fa-layer-group text-primary"></i>
                <h6 class="card-heading">Storefront Sections</h6>
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-1" style="font-size:9.5px;">
                    {{ count($sections) }} {{ Str::plural('Block', count($sections)) }}
                </span>

                <button type="button"
                        class="collapse-toggle-btn shadow-sm"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseSectionsTable"
                        aria-expanded="true"
                        aria-controls="collapseSectionsTable"
                        title="Toggle Sections">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseSectionsTable">

            <!-- Desktop Table -->
            <div class="table-responsive pc-fit-container d-none d-md-block">
                <table class="table builder-table align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3 col-sec-order text-center">Order</th>
                            <th class="col-sec-title">Icon &amp; Title</th>
                            <th class="col-sec-btn">Header Button</th>
                            <th class="col-sec-content">Content</th>
                            <th class="col-sec-status text-center">Status</th>
                            <th class="text-end pe-3 col-sec-actions">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($sections as $sec)
                            <tr>
                                <td class="ps-3 text-center fw-bold font-monospace text-muted bn-digit">
                                    {{ $sec->order_index }}
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        @if(!empty($sec->icon))
                                            <i class="{{ $sec->icon }} flex-shrink-0"
                                               style="color: {{ $sec->title_color ?? '#2563eb' }}; font-size:13px;"></i>
                                        @endif

                                        <div class="min-w-0 flex-grow-1">
                                            <strong class="text-dark d-block text-truncate"
                                                    style="font-size:11.5px;"
                                                    title="{{ $sec->title ?: '(Content Only)' }}">
                                                {{ $sec->title ?: '(Content Only)' }}
                                            </strong>

                                            @if(isset($sec->show_header) && (int)$sec->show_header === 0)
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-1 py-0" style="font-size:8px;">
                                                    Header Off
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if(!empty($sec->btn_text) || !empty($sec->btn_url))
                                        <span class="badge border text-truncate d-inline-block font-monospace px-2 py-1"
                                              style="background-color: {{ $sec->btn_bg ?? '#eff6ff' }}; color: {{ $sec->btn_color ?? '#2563eb' }}; font-size:9.5px; max-width:100%;"
                                              title="{{ $sec->btn_text ?: 'Link' }}">
                                            {{ $sec->btn_text ?: 'Link' }}
                                            <i class="fas fa-external-link-alt ms-1" style="font-size:8px;"></i>
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="text-secondary font-monospace d-inline-block text-truncate"
                                          style="font-size:10px; max-width:100%;"
                                          title="{{ strip_tags($sec->custom_content) }}">
                                        {{ Str::limit(strip_tags($sec->custom_content), 50) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if($sec->is_active)
                                        <span class="status-badge-active">
                                            <i class="fas fa-circle" style="font-size:5px;"></i> Active
                                        </span>
                                    @else
                                        <span class="status-badge-disabled">
                                            <i class="fas fa-circle" style="font-size:5px;"></i> Off
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button"
                                                class="action-btn-circle edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $sec->id }}"
                                                title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </button>

                                        <button type="button"
                                                class="action-btn-circle delete"
                                                onclick="openDeleteConfirmationModal('{{ route('admin.theme.setting.home_builder.destroy', $sec->id) }}', '{{ addslashes($sec->title ?? 'Section') }}')"
                                                title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-cubes d-block"></i>
                                        <p>No sections yet. Add your first section.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="d-block d-md-none p-2">
                @forelse($sections as $sec)
                    <div class="section-item-mobile">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="badge bg-light text-dark border font-monospace bn-digit" style="font-size:9px;">
                                Order: {{ $sec->order_index }}
                            </span>

                            @if($sec->is_active)
                                <span class="status-badge-active">
                                    <i class="fas fa-circle" style="font-size:5px;"></i> Active
                                </span>
                            @else
                                <span class="status-badge-disabled">
                                    <i class="fas fa-circle" style="font-size:5px;"></i> Off
                                </span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                @if(!empty($sec->icon))
                                    <i class="{{ $sec->icon }} flex-shrink-0"
                                       style="color: {{ $sec->title_color ?? '#2563eb' }}; font-size:12px;"></i>
                                @endif

                                <strong class="mobile-section-title text-truncate">
                                    {{ $sec->title ?: '(Content Only)' }}
                                </strong>

                                @if(isset($sec->show_header) && (int)$sec->show_header === 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis flex-shrink-0" style="font-size:8px;">
                                        Header Off
                                    </span>
                                @endif
                            </div>

                            <div class="text-muted font-monospace text-truncate mt-2" style="font-size:10px;">
                                {{ Str::limit(strip_tags($sec->custom_content), 55) }}
                            </div>

                            @if(!empty($sec->btn_text))
                                <div class="mt-1">
                                    <span class="badge bg-light text-primary border" style="font-size:9px;">
                                        <i class="fas fa-link me-1"></i>{{ $sec->btn_text }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="section-item-mobile-actions">
                            <button type="button"
                                    class="btn btn-light border btn-compact"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $sec->id }}">
                                <i class="fas fa-pen text-primary"></i> Edit
                            </button>

                            <button type="button"
                                    class="btn btn-light border btn-compact text-danger"
                                    onclick="openDeleteConfirmationModal('{{ route('admin.theme.setting.home_builder.destroy', $sec->id) }}', '{{ addslashes($sec->title ?? 'Section') }}')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-cubes d-block"></i>
                        <p>No sections found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade builder-modal-scope" id="addSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.theme.setting.home_builder.store') }}" method="POST">
                @csrf

                <div class="modal-header bg-dark text-white py-2 px-3">
                    <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                        <i class="fas fa-plus-circle text-primary"></i>
                        Add Section
                    </h6>

                    <button type="button"
                            class="btn-close btn-close-white shadow-none"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">

                    <!-- Title -->
                    <div class="row g-2 mb-3">
                        <div class="col-8 col-md-7">
                            <label class="form-label">Section Title</label>
                            <input type="text"
                                   name="title"
                                   class="form-control"
                                   placeholder="New Arrivals">
                        </div>

                        <div class="col-4 col-md-2">
                            <label class="form-label">Title Color</label>
                            <input type="color"
                                   name="title_color"
                                   class="form-control form-control-color w-100"
                                   value="#0f172a">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Header Bar</label>
                            <div class="section-setting-box d-flex align-items-center justify-content-between">
                                <span class="form-check-label">Show Header</span>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0"
                                           type="checkbox"
                                           name="show_header"
                                           value="1"
                                           id="addShowHeader"
                                           checked>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon & Order -->
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Icon Class</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">
                                    <i class="fas fa-icons"></i>
                                </span>
                                <input type="text"
                                       name="icon"
                                       class="form-control font-monospace"
                                       placeholder="fas fa-layer-group"
                                       value="fas fa-layer-group">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Display Order</label>
                            <input type="number"
                                   name="order_index"
                                   class="form-control font-monospace bn-digit"
                                   value="10">
                        </div>
                    </div>

                    <!-- Header Button -->
                    <div class="section-setting-box mb-3">
                        <span class="setting-title">
                            <i class="fas fa-link text-primary me-1"></i>
                            Header Button
                        </span>

                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Button Text</label>
                                <input type="text"
                                       name="btn_text"
                                       class="form-control"
                                       placeholder="View All">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Target URL</label>
                                <input type="text"
                                       name="btn_url"
                                       class="form-control font-monospace"
                                       placeholder="/shop">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Background</label>
                                <input type="color"
                                       name="btn_bg"
                                       class="form-control form-control-color w-100"
                                       value="#ffffff">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Text Color</label>
                                <input type="color"
                                       name="btn_color"
                                       class="form-control form-control-color w-100"
                                       value="#2563eb">
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label class="form-label">Shortcode / Content *</label>
                        <textarea name="custom_content"
                                  rows="4"
                                  class="form-control font-monospace"
                                  placeholder='[products limit="8" columns="4"]'
                                  required></textarea>
                    </div>

                    <!-- Active -->
                    <div class="section-setting-box d-flex align-items-center justify-content-between">
                        <label class="form-check-label m-0" for="newActiveSwitch">
                            Active on Storefront
                        </label>

                        <div class="form-check form-switch m-0 ps-0">
                            <input class="form-check-input ms-0"
                                   type="checkbox"
                                   name="is_active"
                                   value="1"
                                   id="newActiveSwitch"
                                   checked>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3 justify-content-between">
                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary btn-compact px-3">
                        <i class="fas fa-save"></i> Save Block
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modals -->
@foreach($sections as $sec)
<div class="modal fade builder-modal-scope" id="editModal{{ $sec->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.theme.setting.home_builder.update', $sec->id) }}" method="POST">
                @csrf

                <div class="modal-header bg-dark text-white py-2 px-3">
                    <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                        <i class="fas fa-pen text-primary"></i>
                        <span class="text-truncate">Edit: {{ $sec->title ?: 'Block #' . $sec->id }}</span>
                    </h6>

                    <button type="button"
                            class="btn-close btn-close-white shadow-none"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">

                    <!-- Title -->
                    <div class="row g-2 mb-3">
                        <div class="col-8 col-md-7">
                            <label class="form-label">Section Title</label>
                            <input type="text"
                                   name="title"
                                   class="form-control"
                                   value="{{ $sec->title }}">
                        </div>

                        <div class="col-4 col-md-2">
                            <label class="form-label">Title Color</label>
                            <input type="color"
                                   name="title_color"
                                   class="form-control form-control-color w-100"
                                   value="{{ $sec->title_color ?? '#0f172a' }}">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Header Bar</label>
                            <div class="section-setting-box d-flex align-items-center justify-content-between">
                                <span class="form-check-label">Show Header</span>
                                <div class="form-check form-switch m-0 ps-0">
                                    <input class="form-check-input ms-0"
                                           type="checkbox"
                                           name="show_header"
                                           value="1"
                                           id="editShowHeader{{ $sec->id }}"
                                           {{ (!isset($sec->show_header) || (int)$sec->show_header === 1) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon & Order -->
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Icon Class</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">
                                    <i class="fas fa-icons"></i>
                                </span>
                                <input type="text"
                                       name="icon"
                                       class="form-control font-monospace"
                                       value="{{ $sec->icon ?? 'fas fa-layer-group' }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Display Order</label>
                            <input type="number"
                                   name="order_index"
                                   class="form-control font-monospace bn-digit"
                                   value="{{ $sec->order_index }}">
                        </div>
                    </div>

                    <!-- Header Button -->
                    <div class="section-setting-box mb-3">
                        <span class="setting-title">
                            <i class="fas fa-link text-primary me-1"></i>
                            Header Button
                        </span>

                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Button Text</label>
                                <input type="text"
                                       name="btn_text"
                                       class="form-control"
                                       value="{{ $sec->btn_text }}"
                                       placeholder="View All">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Target URL</label>
                                <input type="text"
                                       name="btn_url"
                                       class="form-control font-monospace"
                                       value="{{ $sec->btn_url }}"
                                       placeholder="/shop">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Background</label>
                                <input type="color"
                                       name="btn_bg"
                                       class="form-control form-control-color w-100"
                                       value="{{ $sec->btn_bg ?? '#ffffff' }}">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Text Color</label>
                                <input type="color"
                                       name="btn_color"
                                       class="form-control form-control-color w-100"
                                       value="{{ $sec->btn_color ?? '#2563eb' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label class="form-label">Shortcode / Content *</label>
                        <textarea name="custom_content"
                                  rows="4"
                                  class="form-control font-monospace"
                                  required>{{ $sec->custom_content }}</textarea>
                    </div>

                    <!-- Active -->
                    <div class="section-setting-box d-flex align-items-center justify-content-between">
                        <label class="form-check-label m-0" for="act{{ $sec->id }}">
                            Active on Storefront
                        </label>

                        <div class="form-check form-switch m-0 ps-0">
                            <input class="form-check-input ms-0"
                                   type="checkbox"
                                   name="is_active"
                                   value="1"
                                   id="act{{ $sec->id }}"
                                   {{ $sec->is_active ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3 justify-content-between">
                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary btn-compact px-3">
                        <i class="fas fa-save"></i> Update Block
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Delete Confirmation -->
<div class="modal fade builder-modal-scope"
     id="deleteConfirmModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-3 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size:14px;">
                    Delete Section?
                </h6>

                <p class="text-muted small mb-3" style="font-size:11.5px;">
                    Remove <strong id="deleteSectionTitle" class="text-dark">this section</strong>?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <form id="confirmedDeleteForm" action="" method="POST" class="d-inline m-0">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger btn-compact">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div class="modal fade builder-modal-scope"
     id="actionNotificationModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-success">
                    <i class="fas fa-circle-check fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size:14px;">
                    Success
                </h6>

                <p class="text-muted small mb-3"
                   id="notificationMessageText"
                   style="font-size:12px; line-height:1.45;">
                    {{ session('success') }}
                </p>

                <button type="button"
                        class="btn btn-primary btn-compact px-4"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Copy Toast -->
<div id="sc-copy-toast">
    <i class="fas fa-check-circle text-success"></i>
    <span>Shortcode copied!</span>
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

            new bootstrap.Modal(
                document.getElementById('deleteConfirmModal')
            ).show();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            const notifModal = new bootstrap.Modal(
                document.getElementById('actionNotificationModal')
            );

            notifModal.show();
        @endif
    });
</script>
@endsection
