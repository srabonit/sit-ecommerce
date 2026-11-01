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

    $totalFormsCount = method_exists($forms, 'total') ? $forms->total() : $forms->count();
    $activeFormsCount = $forms->where('is_active', true)->count();
    $totalSubmissionsCount = $forms->sum('submissions_count');
    $totalFieldsCount = $forms->sum('fields_count');
@endphp

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Typography & Scoped Root */
    #formBuilderIndexRoot,
    .fb-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Icon Glyphs Protection */
    #formBuilderIndexRoot i,
    #formBuilderIndexRoot .fa,
    #formBuilderIndexRoot .fas,
    #formBuilderIndexRoot .far,
    #formBuilderIndexRoot .fab,
    #formBuilderIndexRoot .fa-solid,
    #formBuilderIndexRoot .fa-regular,
    .fb-modal-scope i,
    .fb-modal-scope .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #formBuilderIndexRoot .far,
    #formBuilderIndexRoot .fa-regular {
        font-weight: 400 !important;
    }

    /* Strict 5px Element Radius Across Controls */
    #formBuilderIndexRoot .btn,
    #formBuilderIndexRoot .btn-sm,
    #formBuilderIndexRoot button,
    #formBuilderIndexRoot .form-control,
    #formBuilderIndexRoot .form-select,
    #formBuilderIndexRoot .badge,
    #formBuilderIndexRoot .input-group-text,
    #formBuilderIndexRoot .card,
    #formBuilderIndexRoot .metric-card,
    #formBuilderIndexRoot .fb-header-card,
    #formBuilderIndexRoot .fb-table-card,
    #formBuilderIndexRoot .shortcode-box,
    #formBuilderIndexRoot .proof-thumb-box,
    #formBuilderIndexRoot .page-link,
    #formBuilderIndexRoot .page-item span {
        border-radius: 5px !important;
    }

    /* Tabular Number Alignment Without Monospace Look */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Compact Buttons with High Contrast Hover States */
    .btn-compact {
        font-size: 11px !important;
        padding: 6px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    .btn-action-square {
        width: 26px !important;
        height: 26px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10px !important;
        border-radius: 5px !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        transition: all 0.15s ease !important;
        cursor: pointer;
    }
    .btn-action-view {
        color: #16a34a !important;
    }
    .btn-action-view:hover {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }
    .btn-action-edit {
        color: #2563eb !important;
    }
    .btn-action-edit:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-action-delete {
        color: #dc2626 !important;
    }
    .btn-action-delete:hover {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
    }

    /* Clean White Theme Header Card */
    .fb-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    /* Metric Cards Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 18px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
        color: #64748b;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    /* Main Container Card */
    .fb-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    /* Table Architecture */
    .custom-fb-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .custom-fb-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 10px;
        white-space: nowrap;
    }
    .custom-fb-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-fb-table tbody tr:hover td {
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

        .col-fb-form      { width: 31% !important; }
        .col-fb-fields    { width: 10% !important; }
        .col-fb-subs      { width: 14% !important; }
        .col-fb-shortcode { width: 22% !important; }
        .col-fb-status    { width: 10% !important; }
        .col-fb-action    { width: 13% !important; }
    }

    /* Modern Shortcode Container (No Monospace Raw Terminal Look) */
    .shortcode-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 600;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }
    .shortcode-box:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .btn-copy-shortcode {
        color: #64748b;
        border: none;
        background: transparent;
        padding: 0;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        transition: color 0.15s ease;
    }
    .btn-copy-shortcode:hover {
        color: #2563eb;
    }

    /* Soft Enterprise Badges */
    .badge-soft-success   { background: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .badge-soft-danger    { background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
    .badge-soft-primary   { background: #eff6ff !important; color: #2563eb !important; border: 1px solid #dbeafe !important; }
    .badge-soft-secondary { background: #f8fafc !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }

    /* Submissions Count Pill */
    .subs-count-pill {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        transition: transform 0.15s ease;
    }
    .subs-count-pill:hover .badge-soft-secondary {
        background: #eff6ff !important;
        color: #2563eb !important;
        border-color: #bfdbfe !important;
    }
    .subs-count-pill:hover {
        transform: translateY(-1px);
    }

    /* Thumbnail Box */
    .proof-thumb-box {
        width: 32px;
        height: 32px;
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

    /* Pagination */
    #fbPaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #fbPaginationLinks nav > div.d-sm-none,
    #fbPaginationLinks nav > .d-sm-none,
    #fbPaginationLinks nav p,
    #fbPaginationLinks nav .small.text-muted {
        display: none !important;
    }
    #fbPaginationLinks .pagination {
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
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

    /* Minimal Modal System */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }
    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }
    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 22px !important;
        line-height: 1.45 !important;
    }
    .btn-minimal-delete {
        background-color: #c93424 !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(201, 52, 36, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-delete:hover {
        background-color: #b02717 !important;
        box-shadow: 0 6px 14px rgba(201, 52, 36, 0.38) !important;
        color: #ffffff !important;
    }
    .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        padding: 7px 14px !important;
        box-shadow: none !important;
        transition: color 0.15s ease !important;
        cursor: pointer !important;
    }
    .btn-minimal-cancel:hover {
        color: #0f172a !important;
        background: transparent !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .fb-header-card {
            padding: 12px 14px !important;
        }
        .fb-header-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .fb-header-actions .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-fb-table {
            min-width: 720px !important;
            table-layout: auto !important;
        }
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formBuilderIndexRoot">

    <!-- 1. Header Banner Card -->
    <div class="fb-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fa-solid fa-rectangle-list text-primary"></i>
                <span>Custom Form Builder</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Custom Forms, Fields & Submissions
            </small>
        </div>

        <div class="fb-header-actions d-flex align-items-center gap-2">
            <a href="{{ route('admin.forms.submissions.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white fw-semibold">
                <i class="fa-solid fa-address-book me-1 text-primary"></i>
                <span>All Submissions</span>
            </a>
            <a href="{{ route('admin.forms.create') }}" class="btn btn-primary btn-compact shadow-xs fw-bold">
                <i class="fa-solid fa-plus me-1"></i>
                <span>Create New Form</span>
            </a>
        </div>
    </div>

    <!-- 2. Form Builder Telemetry KPI Row Overview -->
    <div class="row g-2 mb-3">
        <!-- Total Forms -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Total Forms</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fa-solid fa-rectangle-list"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-tabular stat-val-num">
                        {{ number_format($totalFormsCount) }}
                    </h4>
        
                </div>
            </div>
        </div>

        <!-- Active Forms -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Active Live Forms</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success bn-digit font-tabular stat-val-num">
                        {{ number_format($activeFormsCount) }}
                    </h4>
                
                </div>
            </div>
        </div>

        <!-- Submissions / Leads Collected -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Total Submissions</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-tabular stat-val-num">
                        {{ number_format($totalSubmissionsCount) }}
                    </h4>
                </div>
            </div>
        </div>

        <!-- Dynamic Fields -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Dynamic Fields</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-tabular stat-val-num">
                        {{ number_format($totalFieldsCount) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Main Forms Ledger Card -->
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
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12.5px; line-height: 1.3;" title="{{ $form->name }}">
                                            <a href="{{ route('admin.forms.edit', $form) }}" class="text-dark text-decoration-none">
                                                {{ $form->name }}
                                            </a>
                                        </strong>
                                        <small class="text-muted d-block text-truncate" style="font-size: 10.5px; font-weight: 500;" title="/{{ $form->slug }}">
                                            /{{ $form->slug }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <!-- Fields Count -->
                            <td class="text-center col-fb-fields">
                                <span class="badge badge-soft-primary bn-digit font-tabular px-2 py-1" style="font-size: 10px;">
                                    {{ $form->fields_count ?? ($form->fields ? $form->fields->count() : 0) }}
                                </span>
                            </td>

                            <!-- Submissions Count -->
                            <td class="text-center col-fb-subs">
                                <a href="{{ route('admin.forms.submissions.index', ['form_id' => $form->id]) }}" class="subs-count-pill" title="View Submissions for {{ $form->name }}">
                                    <span class="badge badge-soft-secondary bn-digit font-tabular px-2 py-1" style="font-size: 10px; font-weight: 600;">
                                        <i class="fa-regular fa-folder-open me-1 text-primary"></i>{{ $form->submissions_count ?? 0 }} entries
                                    </span>
                                </a>
                            </td>

                            <!-- Shortcode Box with Clean Button -->
                            <td class="col-fb-shortcode">
                                <div class="shortcode-box">
                                    <span class="text-truncate" id="sc_text_{{ $form->id }}">[form id="{{ $form->id }}"]</span>
                                    <button type="button" class="btn-copy-shortcode" onclick="copyFormShortcode('[form id=&quot;{{ $form->id }}&quot;]')" title="Copy Shortcode">
                                        <i class="fa-regular fa-copy" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="text-center col-fb-status">
                                @if($form->is_active)
                                    <span class="badge badge-soft-success px-2 py-1" style="font-size: 9.5px; font-weight: 700; text-transform: uppercase;">
                                        Active
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger px-2 py-1" style="font-size: 9.5px; font-weight: 700; text-transform: uppercase;">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-3 col-fb-action">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('admin.forms.submissions.index', ['form_id' => $form->id]) }}" class="btn-action-square btn-action-view shadow-xs" title="View Submissions &amp; Leads">
                                        <i class="fa-solid fa-address-book"></i>
                                    </a>

                                    <a href="{{ route('admin.forms.edit', $form) }}" class="btn-action-square btn-action-edit shadow-xs" title="Build Fields &amp; Configure">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <button type="button" class="btn-action-square btn-action-delete shadow-xs" onclick="triggerFormDeleteModal('{{ $form->id }}', '{{ addslashes($form->name) }}')" title="Delete Form">
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
                                <a href="{{ route('admin.forms.create') }}" class="btn btn-primary btn-compact shadow-xs fw-bold">
                                    <i class="fa-solid fa-plus me-1"></i> Create Your First Form
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Footer -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <small class="text-muted bn-digit font-tabular" style="font-size: 11px;">
                Showing {{ $forms->firstItem() ?? 0 }} to {{ $forms->lastItem() ?? 0 }} of {{ $forms->total() }} record(s)
            </small>
            <div id="fbPaginationLinks">
                {{ $forms->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<!-- Modal 1: Minimal Enterprise Delete Confirmation Modal (Matches Screenshot) -->
<div class="modal fade fb-modal-scope" id="formConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title">Are you sure you wanna delete this?</h6>
            <p class="minimal-modal-subtext" id="formConfirmMessage">
                This form and all its logged submissions will be permanently removed.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-delete" id="btnExecuteFormDelete">Delete</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Minimal Notification Modal -->
<div class="modal fade fb-modal-scope" id="formNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="formNoticeTitle">Notification</h6>
            <p class="minimal-modal-subtext" id="formNoticeMessage" style="margin-bottom: 16px !important;"></p>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-delete" style="background-color: #2563eb !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Lightbox Zoom Modal for Form Logos & Media Preview -->
<div class="modal fade fb-modal-scope" id="formMediaZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2 bg-white" style="border-radius: 8px !important;">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="formMediaZoomTitle">
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

    function showFormModalNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('formNoticeTitle');
        const msgEl   = document.getElementById('formNoticeMessage');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        const modalEl = document.getElementById('formNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function triggerFormDeleteModal(formId, formName) {
        activeDeleteFormId = formId;
        const msgEl = document.getElementById('formConfirmMessage');
        if (msgEl) {
            msgEl.innerHTML = 'Form <strong>"' + formName + '"</strong> and all its logged submissions will be permanently removed.';
        }
        
        const modalEl = document.getElementById('formConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.getElementById('btnExecuteFormDelete')?.addEventListener('click', function() {
        if (activeDeleteFormId) {
            const formEl = document.getElementById('delete-form-element-' + activeDeleteFormId);
            if (formEl) {
                formEl.submit();
            }
        }
    });

    function copyFormShortcode(code) {
        navigator.clipboard.writeText(code).then(() => {
            showFormModalNotice('Embed shortcode ' + code + ' copied to clipboard successfully!', 'Copied Successfully');
        }).catch(() => {
            showFormModalNotice('Could not copy shortcode automatically. Please copy manually.', 'Notice');
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