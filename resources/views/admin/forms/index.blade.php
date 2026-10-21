@extends('layouts.admin')

@section('title', 'Form Builder - ' . config('app.name'))

@php
    // Safe thumbnail and media resolver supporting Global Media Library
    $resolveFormMedia = function($path, $fallback = 'https://placehold.co/60x60?text=Form') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #formBuilderIndexRoot .btn,
    #formBuilderIndexRoot .btn-sm,
    #formBuilderIndexRoot button,
    #formBuilderIndexRoot .form-control,
    #formBuilderIndexRoot .form-select,
    #formBuilderIndexRoot .badge,
    #formBuilderIndexRoot .input-group-text,
    #formBuilderIndexRoot .card,
    #formBuilderIndexRoot .fb-header-card,
    #formBuilderIndexRoot .fb-table-card,
    #formBuilderIndexRoot .shortcode-box,
    #formBuilderIndexRoot .proof-thumb-box,
    #formBuilderIndexRoot .page-link,
    #formBuilderIndexRoot .page-item span,
    .fb-modal-scope .modal-content,
    .fb-modal-scope .btn,
    .fb-modal-scope .badge,
    .fb-modal-scope .alert {
        border-radius: 5px !important;
    }

    #formBuilderIndexRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #formBuilderIndexRoot .input-group > .form-control,
    #formBuilderIndexRoot .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
    .btn-compact {
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

    /* Enterprise Surface Header Card */
    .fb-header-card {
        background: #0f172a;
        color: #ffffff;
        padding: 16px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 16px;
    }

    /* Container Card */
    .fb-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-fb-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-fb-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 8px;
        white-space: nowrap;
    }
    .custom-fb-table td {
        padding: 8px 8px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-fb-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-fb-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-fb-table th,
        .custom-fb-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 6 Columns Proportional Distribution Summing to Exactly 100% */
        .col-fb-form      { width: 32% !important; }
        .col-fb-fields    { width: 10% !important; }
        .col-fb-subs      { width: 12% !important; }
        .col-fb-shortcode { width: 24% !important; }
        .col-fb-status    { width: 10% !important; }
        .col-fb-action    { width: 12% !important; }
    }

    /* Shortcode Display Box with Copy Button */
    .shortcode-box {
        background: #f1f5f9;
        border: 1px dashed #cbd5e1;
        padding: 3px 8px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11px;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
    }

    /* Soft Enterprise Badges */
    .badge-soft-success   { background: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .badge-soft-danger    { background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
    .badge-soft-primary   { background: #e0f2fe !important; color: #0284c7 !important; border: 1px solid #bae6fd !important; }
    .badge-soft-secondary { background: #f1f5f9 !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }

    /* Global Media Chooser Thumbnail Box */
    .proof-thumb-box {
        width: 30px;
        height: 30px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Bulletproof Responsive Pagination */
    #fbPaginationLinks {
        display: flex;
        align-items: center;
    }
    #fbPaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #fbPaginationLinks nav > div.d-sm-none,
    #fbPaginationLinks nav > .d-sm-none {
        display: none !important;
    }
    #fbPaginationLinks nav > div.d-none,
    #fbPaginationLinks nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #fbPaginationLinks nav p,
    #fbPaginationLinks nav .small.text-muted,
    #fbPaginationLinks nav div:has(> p),
    #fbPaginationLinks nav > div.d-none > div:first-child,
    #fbPaginationLinks nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #fbPaginationLinks .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #fbPaginationLinks .page-item {
        display: inline-flex !important;
    }
    #fbPaginationLinks .page-item .page-link,
    #fbPaginationLinks .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 25px !important;
        height: 25px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        line-height: 1 !important;
        transition: all 0.12s ease !important;
    }
    #fbPaginationLinks .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #fbPaginationLinks .page-item.active .page-link,
    #fbPaginationLinks .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #fbPaginationLinks .page-item.disabled .page-link,
    #fbPaginationLinks .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #fbPaginationLinks nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .fb-header-card {
            padding: 12px 14px !important;
        }
        .fb-header-actions {
            width: 100%;
        }
        .fb-header-actions .btn {
            width: 100%;
            justify-content: center;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-fb-table {
            min-width: 680px !important;
            table-layout: auto !important;
        }
        #fbPaginationLinks {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }
        #fbPaginationLinks nav {
            justify-content: center !important;
            width: 100% !important;
        }
        #fbPaginationLinks .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
        #fbPaginationLinks .page-item .page-link,
        #fbPaginationLinks .page-item span {
            min-width: 24px !important;
            height: 24px !important;
            font-size: 10.5px !important;
            padding: 0 5px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formBuilderIndexRoot">

    <!-- Header Banner Card -->
    <div class="fb-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fa-solid fa-wand-magic-sparkles text-primary"></i>
                <span>Form Builder Directory</span>
            </h5>
            <small class="text-white-50 d-block" style="font-size: 12px; line-height: 1.35;">
                Create, customize and embed dynamic frontend forms, field workflows &amp; submission handlers.
            </small>
        </div>

        <div class="fb-header-actions">
            <a href="{{ route('admin.forms.create') }}" class="btn btn-primary btn-compact shadow-xs fw-bold">
                <i class="fa-solid fa-plus me-1"></i>
                <span>Create New Form</span>
            </a>
        </div>
    </div>

    <!-- Main Forms Ledger Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
    <div class="fb-table-card overflow-hidden">
        <div class="table-responsive pc-fit-container">
            <table class="table align-middle custom-fb-table mb-0" id="formsListTable">
                <thead>
                    <tr>
                        <th class="ps-3 col-fb-form">Form Identity &amp; Logo</th>
                        <th class="text-center col-fb-fields">Fields</th>
                        <th class="text-center col-fb-subs">Submissions</th>
                        <th class="col-fb-shortcode">Embed Shortcode</th>
                        <th class="text-center col-fb-status">Status</th>
                        <th class="text-end pe-3 col-fb-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                        @php
                            $formLogoRaw = $form->logo ?? ($form->banner ?? null);
                            $formLogoUrl = !empty($formLogoRaw) ? $resolveFormMedia($formLogoRaw) : null;
                        @endphp
                        <tr id="form-row-{{ $form->id }}">
                            <!-- Form Identity & Media Preview -->
                            <td class="ps-3 col-fb-form">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    @if($formLogoUrl)
                                        <div class="proof-thumb-box shadow-xs" onclick="zoomFormMedia('{{ $formLogoUrl }}', '{{ addslashes($form->name) }} Logo')" title="Click to Zoom Logo">
                                            <img src="{{ $formLogoUrl }}" alt="{{ $form->name }}" onerror="this.src='https://placehold.co/60x60?text=Form';">
                                            <div class="proof-thumb-hover-overlay">
                                                <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                            </div>
                                        </div>
                                    @else
                                        <div class="proof-thumb-box bg-light text-secondary border shadow-xs" style="cursor: default;" title="Default Form Icon">
                                            <i class="fa-solid fa-file-lines" style="font-size: 13px;"></i>
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-grow-1">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $form->name }}">
                                            <a href="{{ route('admin.forms.edit', $form) }}" class="text-dark text-decoration-none">
                                                {{ $form->name }}
                                            </a>
                                        </strong>
                                        <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;" title="/{{ $form->slug }}">
                                            /{{ $form->slug }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <!-- Fields Count -->
                            <td class="text-center col-fb-fields">
                                <span class="badge badge-soft-primary font-monospace bn-digit px-2 py-1" style="font-size: 10.5px;">
                                    {{ $form->fields_count ?? ($form->fields ? $form->fields->count() : 0) }}
                                </span>
                            </td>

                            <!-- Submissions Count -->
                            <td class="text-center col-fb-subs">
                                <span class="badge badge-soft-secondary font-monospace bn-digit px-2 py-1" style="font-size: 10.5px;">
                                    {{ $form->submissions_count ?? 0 }} entries
                                </span>
                            </td>

                            <!-- Shortcode Box with Quick 1-Click Copy -->
                            <td class="col-fb-shortcode">
                                <div class="shortcode-box">
                                    <span class="text-truncate" id="sc_text_{{ $form->id }}">[form id="{{ $form->id }}"]</span>
                                    <button type="button" class="btn btn-link btn-xs p-0 text-muted shadow-none" onclick="copyFormShortcode('[form id=&quot;{{ $form->id }}&quot;]')" title="Copy Shortcode">
                                        <i class="fa-regular fa-copy" style="font-size: 10px;"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="text-center col-fb-status">
                                @if($form->is_active)
                                    <span class="badge badge-soft-success px-2 py-1 font-monospace text-uppercase" style="font-size: 9.5px;">
                                        Active
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger px-2 py-1 font-monospace text-uppercase" style="font-size: 9.5px;">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-3 col-fb-action">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <!-- Edit & Build Fields -->
                                    <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-light border text-primary p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; font-size: 10px;" title="Build Fields &amp; Configure">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <!-- Delete Form via Custom Modal -->
                                    <button type="button" class="btn btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; font-size: 10px;" onclick="triggerFormDeleteModal('{{ $form->id }}', '{{ addslashes($form->name) }}')" title="Delete Form">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>

                                    <form id="delete-form-element-{{ $form->id }}" method="POST" action="{{ route('admin.forms.destroy', $form) }}" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="fa-regular fa-rectangle-list fa-2x mb-2 d-block opacity-25"></i>
                                <span class="fw-semibold d-block mb-2">No forms created yet in the system.</span>
                                <a href="{{ route('admin.forms.create') }}" class="btn btn-primary btn-compact shadow-xs">
                                    <i class="fa-solid fa-plus me-1"></i> Create Your First Form
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Footer (Exact Transaction Standard) -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                Showing {{ $forms->firstItem() ?? 0 }} to {{ $forms->lastItem() ?? 0 }} of {{ $forms->total() }} record(s)
            </small>
            <div id="fbPaginationLinks">
                {{ $forms->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<!-- Modal 1: Custom Enterprise Notice / Alert Modal -->
<div class="modal fade fb-modal-scope" id="formNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="formNoticeIconWrap">
                    <i class="fa-solid fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="formNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="formNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Custom Enterprise Delete Confirmation Modal -->
<div class="modal fade fb-modal-scope" id="formConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fa-solid fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="formConfirmTitle" style="font-size: 14.5px;">Delete Confirmation</h6>
                <p class="text-muted small mb-3" id="formConfirmMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs" id="btnExecuteFormDelete">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Lightbox Zoom Modal for Form Logos & Media Preview -->
<div class="modal fade fb-modal-scope" id="formMediaZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="formMediaZoomTitle">
                    <i class="fa-solid fa-image text-primary me-1"></i> Form Media Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="formMediaZoomImgTag" src="" alt="Form Media" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    let activeDeleteFormId = null;

    function showFormModalNotice(message, title = 'Notice', type = 'info') {
        const titleEl = document.getElementById('formNoticeTitle');
        const msgEl   = document.getElementById('formNoticeMessage');
        const iconEl  = document.getElementById('formNoticeIconWrap');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconEl) {
            if (type === 'danger' || type === 'error') {
                iconEl.innerHTML = '<i class="fa-solid fa-circle-xmark text-danger fa-2x"></i>';
            } else if (type === 'success') {
                iconEl.innerHTML = '<i class="fa-solid fa-circle-check text-success fa-2x"></i>';
            } else if (type === 'warning') {
                iconEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning fa-2x"></i>';
            } else {
                iconEl.innerHTML = '<i class="fa-solid fa-circle-info text-primary fa-2x"></i>';
            }
        }

        const modalEl = document.getElementById('formNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function triggerFormDeleteModal(formId, formName) {
        activeDeleteFormId = formId;
        document.getElementById('formConfirmTitle').innerText = 'Delete Form?';
        document.getElementById('formConfirmMessage').innerHTML = 'Are you sure you want to permanently delete <strong>' + formName + '</strong> and all its logged submissions?';
        
        const modalEl = document.getElementById('formConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.getElementById('btnExecuteFormDelete').addEventListener('click', function() {
        if (activeDeleteFormId) {
            const formEl = document.getElementById('delete-form-element-' + activeDeleteFormId);
            if (formEl) {
                formEl.submit();
            }
        }
    });

    function copyFormShortcode(code) {
        navigator.clipboard.writeText(code).then(() => {
            showFormModalNotice('Embed shortcode ' + code + ' copied to clipboard successfully!', 'Copied Successfully', 'success');
        }).catch(() => {
            showFormModalNotice('Could not copy shortcode automatically. Please copy manually.', 'Notice', 'warning');
        });
    }

    function zoomFormMedia(url, title = 'Form Media') {
        const titleEl = document.getElementById('formMediaZoomTitle');
        const imgTag  = document.getElementById('formMediaZoomImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('formMediaZoomModal')).show();
        }
    }
</script>
@endsection