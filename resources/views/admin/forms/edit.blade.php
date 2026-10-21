@extends('layouts.admin')

@section('title', 'Form Builder - ' . $form->name . ' - ' . config('app.name'))

@php
    // Safe media resolver supporting Global Media Library paths
    $resolveFormMedia = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $currentLogoRaw = $form->logo ?? ($form->banner ?? null);
    $currentLogoUrl = !empty($currentLogoRaw) ? $resolveFormMedia($currentLogoRaw) : null;
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #formBuilderStudioRoot .btn,
    #formBuilderStudioRoot .btn-sm,
    #formBuilderStudioRoot button,
    #formBuilderStudioRoot .form-control,
    #formBuilderStudioRoot .form-select,
    #formBuilderStudioRoot .input-group-text,
    #formBuilderStudioRoot .badge,
    #formBuilderStudioRoot .nav-pills .nav-link,
    #formBuilderStudioRoot .card,
    #formBuilderStudioRoot .studio-header-card,
    #formBuilderStudioRoot .studio-panel-card,
    #formBuilderStudioRoot .field-draggable-item,
    #formBuilderStudioRoot .shortcode-pill,
    #formBuilderStudioRoot .proof-media-surface,
    #formBuilderStudioRoot .proof-thumb-box,
    #formBuilderStudioRoot .form-preview-card,
    .builder-modal-scope .modal-content,
    .builder-modal-scope .btn,
    .builder-modal-scope .form-control,
    .builder-modal-scope .form-select,
    .builder-modal-scope .badge,
    .builder-modal-scope .nav-tabs .nav-link {
        border-radius: 5px !important;
    }

    #formBuilderStudioRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #formBuilderStudioRoot .input-group > .form-control,
    #formBuilderStudioRoot .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
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

    /* Studio Header Banner */
    .studio-header-card {
        background: #0f172a;
        color: #ffffff;
        padding: 16px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 16px;
    }

    .shortcode-pill {
        background: rgba(255, 255, 255, 0.08);
        border: 1px dashed rgba(255, 255, 255, 0.25);
        padding: 6px 12px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    /* Navigation Tabs Strip */
    .studio-nav-strip {
        display: flex;
        flex-wrap: nowrap;
        gap: 6px;
        overflow-x: auto;
        padding: 2px 0 8px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .studio-nav-strip::-webkit-scrollbar {
        display: none;
    }
    .studio-nav-pill {
        font-size: 12px;
        font-weight: 600;
        padding: 7px 14px;
        color: #475569;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .studio-nav-pill:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .studio-nav-pill.active {
        color: #ffffff !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }

    /* Main Surface Container Cards */
    .studio-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
        overflow: hidden;
    }
    .studio-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        font-weight: 700;
        font-size: 13px;
        color: #1e293b;
    }
    .studio-card-body {
        padding: 16px;
    }

    /* Form Controls */
    .form-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #formBuilderStudioRoot .form-control,
    #formBuilderStudioRoot .form-select,
    .builder-modal-scope .form-control,
    .builder-modal-scope .form-select {
        min-height: 33px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #formBuilderStudioRoot .form-control:focus,
    #formBuilderStudioRoot .form-select:focus,
    .builder-modal-scope .form-control:focus,
    .builder-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    textarea.form-control {
        min-height: 80px;
    }

    /* Draggable Field Item in Builder */
    .field-draggable-item {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        margin-bottom: 8px;
        transition: all 0.15s ease;
    }
    .field-draggable-item:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .field-header-row {
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: grab;
    }
    .drag-grip-icon {
        color: #94a3b8;
        font-size: 14px;
        cursor: grab;
        padding: 4px;
    }
    .field-type-icon {
        width: 32px;
        height: 32px;
        border-radius: 5px !important;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    .field-main-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 12px;
    }
    .field-meta-tags {
        font-size: 10px;
        color: #64748b;
        font-family: ui-monospace, SFMono-Regular, monospace;
    }
    .field-item-actions {
        margin-left: auto;
        display: flex;
        gap: 4px;
    }
    .field-action-square-btn {
        width: 26px;
        height: 26px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 11px;
        border-radius: 5px !important;
        transition: all 0.12s ease;
    }
    .field-action-square-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    /* Add Field Button */
    .add-field-action-btn {
        width: 100%;
        padding: 10px;
        border: 1.5px dashed #cbd5e1;
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .add-field-action-btn:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: #eff6ff;
    }

    /* Global Media Chooser Component Styles */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
        background: #f1f5f9;
    }
    .proof-thumb-box {
        width: 38px;
        height: 38px;
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

    /* Live Interactive Form Preview Card */
    .form-preview-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .preview-header-area {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 12px;
        margin-bottom: 14px;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .studio-header-card {
            padding: 12px 14px !important;
        }
        .shortcode-pill {
            width: 100%;
            justify-content: space-between;
        }
        .studio-card-body {
            padding: 12px !important;
        }
        .field-header-row {
            padding: 8px 10px;
        }
        .settings-toggle-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formBuilderStudioRoot">

    <!-- 1. Top Header Banner -->
    <div class="studio-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <a href="{{ route('admin.forms.index') }}" class="btn btn-sm btn-outline-secondary btn-compact text-white border-white-50 shadow-xs">
                    <i class="fa-solid fa-arrow-left me-1"></i> Directory
                </a>
                <span class="badge bg-light text-dark font-monospace bn-digit px-2 py-0.5" style="font-size: 10px;">Form #{{ $form->id }}</span>
                <span class="badge {{ $form->is_active ? 'bg-success' : 'bg-secondary' }} font-monospace text-uppercase" style="font-size: 9.5px;">
                    {{ $form->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fa-solid fa-wand-magic-sparkles text-primary"></i>
                <span>{{ $form->name }}</span>
            </h5>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="shortcode-pill shadow-xs">
                <span class="text-truncate font-monospace" id="headerShortcodeText">[form id="{{ $form->id }}"]</span>
                <button type="button" class="btn btn-link btn-xs p-0 text-white shadow-none" onclick="copyStudioShortcode('[form id=&quot;{{ $form->id }}&quot;]')" title="Copy Embed Shortcode">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="openStudioFullscreenPreview()">
                <i class="fa-solid fa-eye me-1"></i> Live Preview
            </button>
        </div>
    </div>

    <!-- 2. Studio Navigation Tabs Strip -->
    <div class="studio-nav-strip mb-3">
        <a href="#tab-fields" class="studio-nav-pill active" data-bs-toggle="pill" data-bs-target="#tab-fields" role="tab">
            <i class="fa-solid fa-layer-group text-primary"></i>
            <span>Form Fields Builder</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace bn-digit px-1.5 py-0" style="font-size: 9.5px;" id="badgeFieldsCount">
                {{ $form->fields->count() }}
            </span>
        </a>
        <a href="#tab-settings" class="studio-nav-pill" data-bs-toggle="pill" data-bs-target="#tab-settings" role="tab">
            <i class="fa-solid fa-gear text-secondary"></i>
            <span>General Settings &amp; Media</span>
        </a>
        <a href="#tab-notifications" class="studio-nav-pill" data-bs-toggle="pill" data-bs-target="#tab-notifications" role="tab">
            <i class="fa-solid fa-envelope-open-text text-info"></i>
            <span>Emails &amp; Autoresponder</span>
        </a>
        <a href="#tab-appearance" class="studio-nav-pill" data-bs-toggle="pill" data-bs-target="#tab-appearance" role="tab">
            <i class="fa-solid fa-palette text-warning"></i>
            <span>Appearance &amp; Theme</span>
        </a>
        <a href="#tab-security" class="studio-nav-pill" data-bs-toggle="pill" data-bs-target="#tab-security" role="tab">
            <i class="fa-solid fa-shield-halved text-danger"></i>
            <span>Anti-Spam &amp; Security</span>
        </a>
        <a href="#tab-integrations" class="studio-nav-pill" data-bs-toggle="pill" data-bs-target="#tab-integrations" role="tab">
            <i class="fa-solid fa-network-wired text-success"></i>
            <span>Webhooks &amp; Integrations</span>
        </a>
    </div>

    <!-- 3. Studio Tab Content Panes -->
    <div class="tab-content">

        <!-- TAB 1: FORM FIELDS BUILDER & LIVE PREVIEW WORKSPACE -->
        <div class="tab-pane fade show active" id="tab-fields" role="tabpanel">
            <div class="row g-3">
                <!-- Left Column: Drag & Drop Field Reordering and Management -->
                <div class="col-12 col-lg-7">
                    <div class="studio-panel-card">
                        <div class="studio-card-header d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-bars-staggered me-1 text-primary"></i> Form Fields (Drag to Reorder)</span>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-compact py-0 px-2 shadow-xs" style="height: 24px; font-size: 10px !important;" onclick="refreshFieldListOrder()">
                                    <i class="fa-solid fa-rotate me-1"></i> Sync
                                </button>
                                <button type="button" class="btn btn-primary btn-compact py-0 px-2 shadow-xs fw-bold" style="height: 24px; font-size: 10px !important;" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="prepareAddFieldModal()">
                                    <i class="fa-solid fa-plus me-1"></i> Add Field
                                </button>
                            </div>
                        </div>

                        <div class="studio-card-body">
                            <div id="field-list">
                                @forelse($form->fields as $field)
                                    <div class="field-draggable-item shadow-xs" data-id="{{ $field->id }}" id="field-card-{{ $field->id }}">
                                        <div class="field-header-row">
                                            <div class="drag-grip-icon" title="Drag to reorder position">
                                                <i class="fa-solid fa-grip-vertical"></i>
                                            </div>

                                            <div class="field-type-icon">
                                                <i class="fa-solid
                                                    @switch($field->type)
                                                        @case('text') fa-font @break
                                                        @case('email') fa-envelope @break
                                                        @case('phone') fa-phone @break
                                                        @case('number') fa-hashtag @break
                                                        @case('textarea') fa-align-left @break
                                                        @case('select') fa-list @break
                                                        @case('radio') fa-circle-dot @break
                                                        @case('checkbox') fa-square-check @break
                                                        @case('file') fa-file-arrow-up @break
                                                        @case('date') fa-calendar @break
                                                        @case('time') fa-clock @break
                                                        @case('url') fa-link @break
                                                        @default fa-i-cursor
                                                    @endswitch
                                                "></i>
                                            </div>

                                            <div class="min-w-0 flex-grow-1">
                                                <div class="field-main-title text-truncate">
                                                    {{ $field->label }}
                                                    @if($field->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </div>
                                                <div class="field-meta-tags text-truncate">
                                                    <span class="badge bg-light text-dark border px-1 py-0">{{ strtoupper($field->type) }}</span>
                                                    <span>{{ $field->name }}</span>
                                                    <span>•</span>
                                                    <span>Width: {{ $field->column_width ?? 12 }}/12</span>
                                                </div>
                                            </div>

                                            <div class="field-item-actions flex-shrink-0">
                                                <button type="button" class="field-action-square-btn text-secondary shadow-xs" onclick="cloneExistingField({{ $field->id }})" title="Duplicate Field">
                                                    <i class="fa-regular fa-clone"></i>
                                                </button>
                                                <button type="button" class="field-action-square-btn text-primary edit-field shadow-xs" data-field='@json($field)' title="Configure Field">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button" class="field-action-square-btn text-danger delete-field shadow-xs" data-id="{{ $field->id }}" title="Delete Field">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted small border bg-light" id="empty-fields-placeholder" style="border-radius: 5px;">
                                        <i class="fa-regular fa-square-plus fa-2x mb-2 d-block opacity-50"></i>
                                        <strong class="d-block text-dark">No form fields configured yet.</strong>
                                        <p class="mb-2" style="font-size: 11px;">Start building your dynamic form by adding input fields below.</p>
                                        <button type="button" class="btn btn-primary btn-compact shadow-xs" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="prepareAddFieldModal()">
                                            <i class="fa-solid fa-plus me-1"></i> Add First Field
                                        </button>
                                    </div>
                                @endforelse
                            </div>

                            <button type="button" class="add-field-action-btn mt-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="prepareAddFieldModal()">
                                <i class="fa-solid fa-plus me-1"></i> Add Another Field
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Live Frontend Simulator -->
                <div class="col-12 col-lg-5">
                    <div class="form-preview-card" style="border-radius: 5px;">
                        <div class="preview-header-area d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-uppercase" style="font-size: 9px;">
                                    LIVE SIMULATOR
                                </span>
                                <h6 class="fw-bold text-dark mb-0 mt-1" style="font-size: 13px;">Frontend Layout Preview</h6>
                            </div>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Reactive Preview</small>
                        </div>

                        <div id="previewSimulatorLogoBox" class="text-center mb-2 {{ $currentLogoUrl ? 'd-block' : 'd-none' }}">
                            <img id="previewSimulatorLogoImg" src="{{ $currentLogoUrl }}" alt="Logo" class="img-fluid border p-1 shadow-xs" style="max-height: 50px; border-radius: 5px;">
                        </div>

                        <div class="text-center mb-3">
                            <h5 class="fw-bold text-dark mb-1" id="previewSimTitle" style="font-size: 15px;">{{ $form->name }}</h5>
                            @if(!empty($form->description))
                                <small class="text-muted d-block" id="previewSimDesc" style="font-size: 11px;">{{ $form->description }}</small>
                            @endif
                        </div>

                        <form onsubmit="event.preventDefault(); showStudioModalNotice('This is an interactive live simulator preview. All fields validate dynamically.', 'Simulator Active', 'info');">
                            <div class="row g-2" id="liveFormFieldsSimulator">
                                @forelse($form->fields as $f)
                                    <div class="col-{{ $f->column_width ?? 12 }} mb-1">
                                        <label class="form-label mb-1" style="font-size: 11px;">
                                            {{ $f->label }}
                                            @if($f->is_required)<span class="text-danger">*</span>@endif
                                        </label>
                                        @if($f->type === 'textarea')
                                            <textarea class="form-control" rows="2" placeholder="{{ $f->placeholder }}" style="font-size: 11.5px;"></textarea>
                                        @elseif($f->type === 'select')
                                            <select class="form-select" style="font-size: 11.5px;">
                                                <option value="">{{ $f->placeholder ?: '-- Select Option --' }}</option>
                                                @if(is_array($f->options))
                                                    @foreach($f->options as $opt)
                                                        <option value="{{ $opt['value'] ?? $opt }}">{{ $opt['label'] ?? $opt }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @elseif($f->type === 'checkbox')
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox">
                                                <label class="form-check-label" style="font-size: 11px;">{{ $f->placeholder ?: 'I accept and confirm' }}</label>
                                            </div>
                                        @else
                                            <input type="{{ in_array($f->type, ['text', 'email', 'phone', 'number', 'date', 'time', 'url']) ? $f->type : 'text' }}" class="form-control" placeholder="{{ $f->placeholder }}" style="font-size: 11.5px;">
                                        @endif
                                        @if(!empty($f->help_text))
                                            <small class="text-muted d-block" style="font-size: 9.5px;">{{ $f->help_text }}</small>
                                        @endif
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-4 text-muted small">
                                        Fields added on the left will render reactively here.
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-3 text-center">
                                <button type="submit" class="btn btn-primary btn-compact w-100 shadow-xs fw-bold" style="min-height: 34px;">
                                    <span>Submit Request</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: GENERAL SETTINGS & 200% GLOBAL MEDIA CHOOSER -->
        <div class="tab-pane fade" id="tab-settings" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-gear me-1 text-primary"></i> General Settings &amp; Brand Media</span>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">CONFIG</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="logo" id="edit_form_logo_media" value="{{ $form->logo ?? '' }}">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Form Title / Display Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ $form->name }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Form URL Slug</label>
                                <input type="text" name="slug" class="form-control font-monospace" value="{{ $form->slug }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description / Instructions</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Brief note explaining the purpose of this form to visitors...">{{ $form->description }}</textarea>
                            </div>

                            <!-- Global Media Chooser Component: Form Logo / Header Banner -->
                            <div class="col-12">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-paperclip text-primary me-1"></i> Form Header Banner / Logo (Global Media):</span>
                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                                </label>
                                <div class="proof-media-surface">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseFormEditLogoMedia()">
                                            <i class="fa-solid fa-photo-film me-1"></i>
                                            <span>Choose from Media Library</span>
                                        </button>
                                        <span class="small text-muted" style="font-size: 11px;">Attach high-res header banner or company logo</span>
                                    </div>

                                    <div id="edit_form_logo_preview_wrap" class="mt-2 {{ $currentLogoUrl ? 'd-flex' : 'd-none' }} align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                        <div class="proof-thumb-box shadow-xs" onclick="zoomFormEditLogoPreview()" title="Click to Zoom Image">
                                            <img id="edit_form_logo_preview_img" src="{{ $currentLogoUrl }}" alt="Logo">
                                            <div class="proof-thumb-hover-overlay">
                                                <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 8.5px;">
                                                <i class="fas fa-check-circle me-1"></i>Media Attached
                                            </span>
                                            <small class="text-muted d-block text-truncate font-monospace" id="edit_form_logo_preview_txt" style="font-size: 9.5px;">{{ $form->logo ?? '' }}</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2" style="font-size: 9.5px !important;" onclick="clearFormEditLogoMedia()">
                                            <i class="fa-solid fa-trash-can me-1"></i>Clear
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Success Message</label>
                                <input type="text" name="success_message" class="form-control" value="{{ $form->success_message }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Redirect URL (Optional)</label>
                                <input type="text" name="redirect_url" class="form-control font-monospace" value="{{ $form->redirect_url }}" placeholder="/thank-you">
                            </div>

                            <div class="col-12">
                                <label class="form-label mb-2">Form Behavior Toggles</label>
                                <div class="d-flex flex-wrap gap-3 p-2 border bg-light settings-toggle-grid" style="border-radius: 5px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="chk_settings_active" value="1" {{ $form->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="chk_settings_active">Active Form Status</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="save_submissions" id="chk_settings_save" value="1" {{ $form->save_submissions ? 'checked' : '' }}>
                                        <label class="form-check-label" for="chk_settings_save">Save User Submissions in Database</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 3: EMAILS, NOTIFICATIONS & AUTORESPONDER -->
        <div class="tab-pane fade" id="tab-notifications" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-envelope-open-text me-1 text-info"></i> Notification Routing &amp; User Confirmation</span>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">EMAIL TEMPLATES</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6 border-end">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13px;">
                                    <i class="fa-solid fa-user-shield text-primary me-1"></i> Staff Notification Alerts
                                </h6>
                                <div class="mb-2">
                                    <label class="form-label">Recipient Admin Emails (Comma separated)</label>
                                    <input type="text" name="admin_email" class="form-control font-monospace" value="{{ $form->admin_email }}" placeholder="admin@example.com, sales@example.com">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Admin Email Subject</label>
                                    <input type="text" name="email_subject" class="form-control" value="{{ $form->email_subject }}">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="send_admin_email" id="chk_send_admin_notif" value="1" {{ $form->send_admin_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chk_send_admin_notif">Dispatch email to staff on every submission</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13px;">
                                    <i class="fa-solid fa-reply text-success me-1"></i> User Instant Confirmation
                                </h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="send_user_email" id="chk_send_user_confirm" value="1" {{ $form->send_user_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chk_send_user_confirm">Send instant confirmation receipt to applicant</label>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">User Email Subject</label>
                                    <input type="text" name="user_email_subject" class="form-control" value="{{ $form->user_email_subject ?? 'Thank you for contacting us!' }}">
                                </div>
                                <div class="p-2 bg-light border small text-muted font-monospace" style="font-size: 10px; border-radius: 5px;">
                                    Available Dynamic Tags: <span class="text-primary">{form_name}</span>, <span class="text-primary">{all_fields}</span>, <span class="text-primary">{submission_id}</span>, <span class="text-primary">{date}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 4: APPEARANCE & THEME STYLING -->
        <div class="tab-pane fade" id="tab-appearance" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-palette me-1 text-warning"></i> Form Layout &amp; Theme Appearance</span>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">THEME ENGINE</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Form Theme Preset</label>
                                <select name="theme_preset" class="form-select">
                                    <option value="modern_light" selected>Enterprise Modern Light</option>
                                    <option value="minimal">Minimal Flat Clean</option>
                                    <option value="soft_rounded">Soft Corporate Shadow</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Submit Button Label</label>
                                <input type="text" name="submit_button_text" class="form-control" value="{{ $form->submit_button_text ?? 'Submit Request' }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Submit Button Theme</label>
                                <select name="submit_button_class" class="form-select">
                                    <option value="btn-primary" selected>Primary Blue</option>
                                    <option value="btn-dark">Dark Charcoal</option>
                                    <option value="btn-success">Emerald Green</option>
                                    <option value="btn-warning">Amber Gold</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Custom Form CSS Overrides</label>
                                <textarea name="custom_css" class="form-control font-monospace" rows="3" placeholder=".my-custom-form { max-width: 600px; margin: auto; }">{{ $form->custom_css ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Appearance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 5: ANTI-SPAM & SECURITY -->
        <div class="tab-pane fade" id="tab-security" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-shield-halved me-1 text-danger"></i> Anti-Spam &amp; Rate Limiting Rules</span>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">SECURITY SHIELD</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Spam Protection Engine</label>
                                <select name="captcha_type" class="form-select">
                                    <option value="honeypot" selected>Silent Honeypot Trap (Recommended - Zero user friction)</option>
                                    <option value="recaptcha_v3">Google reCAPTCHA v3 (Invisible)</option>
                                    <option value="cloudflare_turnstile">Cloudflare Turnstile</option>
                                    <option value="none">No Spam Protection</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Max Submissions per IP / Hour</label>
                                <input type="number" name="rate_limit_per_ip" class="form-control font-monospace bn-digit" value="{{ $form->rate_limit_per_ip ?? 15 }}" min="1">
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="prevent_duplicates" id="chk_prevent_dups" value="1" checked>
                                    <label class="form-check-label" for="chk_prevent_dups">Block rapid duplicate submissions with identical payload</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Security Rules
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 6: WEBHOOKS & INTEGRATIONS -->
        <div class="tab-pane fade" id="tab-integrations" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-network-wired me-1 text-success"></i> Webhook Relays &amp; Zapier Relay</span>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">API WEBHOOK</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Outgoing Webhook URL</label>
                                <input type="url" name="webhook_url" class="form-control font-monospace" value="{{ $form->webhook_url ?? '' }}" placeholder="https://hooks.zapier.com/hooks/catch/...">
                                <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                                    Every submission will post a JSON payload to this endpoint in real-time.
                                </small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">HTTP Method</label>
                                <select name="webhook_method" class="form-select font-monospace">
                                    <option value="POST" selected>POST (Application/JSON)</option>
                                    <option value="PUT">PUT</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Custom Authorization Header (Optional)</label>
                                <input type="text" name="webhook_auth_header" class="form-control font-monospace" value="{{ $form->webhook_auth_header ?? '' }}" placeholder="Bearer YOUR_API_TOKEN">
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Integrations
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL 1: ADVANCED FIELD BUILDER & CONDITIONAL LOGIC MODAL -->
<div class="modal fade builder-modal-scope" id="fieldModal" tabindex="-1" style="z-index: 107000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" id="fieldModalTitle" style="font-size: 13.5px;">
                    <i class="fa-solid fa-plus text-primary"></i> <span>Add New Form Field</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form id="fieldForm">
                <div class="modal-body p-3">
                    <input type="hidden" id="field_id">

                    <ul class="nav nav-pills mb-3 border-bottom pb-2" id="fieldModalTabNav" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-1 px-3 fw-bold" id="fm-tab-basic-btn" data-bs-toggle="pill" data-bs-target="#fm-tab-basic" type="button" role="tab" style="font-size: 11.5px;">
                                1. General &amp; Type
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3 fw-bold" id="fm-tab-rules-btn" data-bs-toggle="pill" data-bs-target="#fm-tab-rules" type="button" role="tab" style="font-size: 11.5px;">
                                2. Validation Rules
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3 fw-bold" id="fm-tab-logic-btn" data-bs-toggle="pill" data-bs-target="#fm-tab-logic" type="button" role="tab" style="font-size: 11.5px;">
                                3. Conditional Logic
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="fieldModalTabContent">
                        <!-- Sub-tab 1: General & Type -->
                        <div class="tab-pane fade show active" id="fm-tab-basic" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Field Type *</label>
                                    <select id="field_type" name="type" class="form-select" required>
                                        <optgroup label="Standard Inputs">
                                            <option value="text">Text Input</option>
                                            <option value="email">Email</option>
                                            <option value="phone">Phone / Mobile</option>
                                            <option value="number">Number</option>
                                            <option value="textarea">Textarea (Multiline)</option>
                                        </optgroup>
                                        <optgroup label="Choice & Select">
                                            <option value="select">Dropdown Select</option>
                                            <option value="radio">Radio Buttons</option>
                                            <option value="checkbox">Single Checkbox</option>
                                        </optgroup>
                                        <optgroup label="File & Date">
                                            <option value="file">File Upload</option>
                                            <option value="date">Date Picker</option>
                                            <option value="time">Time Picker</option>
                                            <option value="url">Website URL</option>
                                        </optgroup>
                                        <optgroup label="Special & Hidden">
                                            <option value="hidden">Hidden Input</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Field Label *</label>
                                    <input type="text" id="field_label" name="label" class="form-control" placeholder="e.g. Your Full Name" required onkeyup="autoGenerateFieldName(this.value)">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Database Field Key Name *</label>
                                    <input type="text" id="field_name" name="name" class="form-control font-monospace" placeholder="e.g. full_name" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Placeholder Text</label>
                                    <input type="text" id="field_placeholder" name="placeholder" class="form-control" placeholder="e.g. Enter your name...">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Default Value</label>
                                    <input type="text" id="field_default" name="default_value" class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Grid Width (Bootstrap Col)</label>
                                    <select id="field_width" name="column_width" class="form-select font-monospace">
                                        <option value="12">100% (12 Columns Full Width)</option>
                                        <option value="6">50% (6 Columns Half Width)</option>
                                        <option value="4">33% (4 Columns 1/3rd)</option>
                                        <option value="3">25% (3 Columns 1/4th)</option>
                                    </select>
                                </div>

                                <div class="col-12" id="options-wrapper" style="display:none;">
                                    <label class="form-label">Options (One option per line: Label|value)</label>
                                    <textarea id="field_options" name="options" class="form-control font-monospace" rows="3" placeholder="Option 1|option_1&#10;Option 2|option_2"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Help / Instruction Text</label>
                                    <input type="text" id="field_help" name="help_text" class="form-control" placeholder="Instruction text displayed right below the input">
                                </div>
                            </div>
                        </div>

                        <!-- Sub-tab 2: Validation Rules -->
                        <div class="tab-pane fade" id="fm-tab-rules" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Min Length</label>
                                    <input type="number" id="field_min_length" name="min_length" class="form-control font-monospace bn-digit">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Max Length</label>
                                    <input type="number" id="field_max_length" name="max_length" class="form-control font-monospace bn-digit">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Min Number Value</label>
                                    <input type="number" step="any" id="field_min_value" name="min_value" class="form-control font-monospace bn-digit">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Max Number Value</label>
                                    <input type="number" step="any" id="field_max_value" name="max_value" class="form-control font-monospace bn-digit">
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="d-flex flex-wrap gap-3 p-2 border bg-light" style="border-radius: 5px;">
                                        <div class="form-check">
                                            <input type="checkbox" id="field_required" name="is_required" value="1" class="form-check-input">
                                            <label class="form-check-label" for="field_required">Mandatory Required Field</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="field_active" name="is_active" value="1" class="form-check-input" checked>
                                            <label class="form-check-label" for="field_active">Active &amp; Rendered</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-tab 3: Conditional Logic -->
                        <div class="tab-pane fade" id="fm-tab-logic" role="tabpanel">
                            <div class="p-3 bg-light border text-muted small" style="border-radius: 5px;">
                                <div class="d-flex align-items-center gap-1 text-primary fw-bold mb-2">
                                    <i class="fa-solid fa-code-branch"></i> Conditional Field Display Logic
                                </div>
                                <p class="mb-2" style="font-size: 11.5px;">Show or hide this field based on applicant responses in previous fields.</p>
                                <div class="row g-2">
                                    <div class="col-12 col-md-4">
                                        <select class="form-select font-monospace" id="field_cond_action">
                                            <option value="show">Show this field IF</option>
                                            <option value="hide">Hide this field IF</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <input type="text" class="form-control font-monospace" id="field_cond_target" placeholder="Target Field Name">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <input type="text" class="form-control" id="field_cond_value" placeholder="Matches Value">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" id="saveFieldBtn">
                        <i class="fa-solid fa-check-circle me-1"></i> Save Field
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: LIGHTBOX ZOOM PREVIEW FOR LOGO & COVERS -->
<div class="modal fade builder-modal-scope" id="formEditLogoZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fa-solid fa-image text-primary me-1"></i> Form Header Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="formEditLogoZoomImgTag" src="" alt="Logo Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: FULLSCREEN LIVE PREVIEW SIMULATOR MODAL -->
<div class="modal fade builder-modal-scope" id="studioFullscreenPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold mb-0" style="font-size: 13.5px;">
                    <i class="fa-solid fa-desktop text-primary me-1"></i> Live Frontend Simulator View
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="bg-white border p-4 shadow-sm mx-auto" style="max-width: 680px; border-radius: 5px;">
                    <div class="text-center mb-3">
                        <h4 class="fw-bold text-dark mb-1">{{ $form->name }}</h4>
                        <p class="text-muted small mb-0">{{ $form->description }}</p>
                    </div>
                    <div id="fullscreenModalRenderZone"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: CUSTOM ENTERPRISE NOTICE / ALERT MODAL -->
<div class="modal fade builder-modal-scope" id="studioNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 109000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="studioNoticeIconWrap">
                    <i class="fa-solid fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="studioNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="studioNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 5: CUSTOM ENTERPRISE CONFIRMATION MODAL -->
<div class="modal fade builder-modal-scope" id="studioConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 109100;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger" id="studioConfirmIconWrap">
                    <i class="fa-solid fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="studioConfirmTitle" style="font-size: 14.5px;">Confirmation</h6>
                <p class="text-muted small mb-3" id="studioConfirmMessage" style="font-size: 12px; line-height: 1.45;">Are you sure you want to proceed?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs" id="btnExecuteStudioConfirmAction">Yes, Proceed</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

<script>
let studioPendingConfirmCallback = null;

// Modal-based Notice and Confirmation Functions
function showStudioModalNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('studioNoticeTitle');
    const msgEl   = document.getElementById('studioNoticeMessage');
    const iconEl  = document.getElementById('studioNoticeIconWrap');

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

    const modalEl = document.getElementById('studioNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function showStudioModalConfirm(title, message, callback, btnClass = 'btn-danger', btnText = 'Yes, Proceed') {
    document.getElementById('studioConfirmTitle').innerText = title;
    document.getElementById('studioConfirmMessage').innerHTML = message;
    
    const confirmBtn = document.getElementById('btnExecuteStudioConfirmAction');
    confirmBtn.className = 'btn ' + btnClass + ' btn-compact shadow-xs';
    confirmBtn.innerText = btnText;

    studioPendingConfirmCallback = callback;

    const modalEl = document.getElementById('studioConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.getElementById('btnExecuteStudioConfirmAction').addEventListener('click', function() {
    const modalEl = document.getElementById('studioConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getInstance(modalEl)?.hide();
    }
    if (typeof studioPendingConfirmCallback === 'function') {
        studioPendingConfirmCallback();
        studioPendingConfirmCallback = null;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const formId = {{ $form->id }};
    const fieldModalEl = document.getElementById('fieldModal');
    const fieldModal = new bootstrap.Modal(fieldModalEl);
    const fieldForm = document.getElementById('fieldForm');
    const fieldList = document.getElementById('field-list');
    let editMode = false;
    let editingFieldId = null;

    // 1. Sortable Drag & Drop Reordering Engine
    if (fieldList) {
        new Sortable(fieldList, {
            animation: 180,
            handle: '.drag-grip-icon',
            filter: '#empty-fields-placeholder',
            onEnd: function () {
                const ids = [];
                fieldList.querySelectorAll('.field-draggable-item').forEach(function (item) {
                    ids.push(item.dataset.id);
                });
                if (!ids.length) return;

                fetch("{{ route('admin.forms.fields.reorder', $form) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ fields: ids })
                })
                .then(r => r.json())
                .then(() => {
                    showStudioModalNotice('Fields sequence updated successfully.', 'Reordered Successfully', 'success');
                });
            }
        });
    }

    // 2. Dynamic Field Type Option Display Toggle
    const fieldType = document.getElementById('field_type');
    function toggleOptionsDisplay() {
        const type = fieldType.value;
        const optionsWrapper = document.getElementById('options-wrapper');
        const optionTypes = ['select', 'radio', 'checkbox'];
        optionsWrapper.style.display = optionTypes.includes(type) ? 'block' : 'none';
    }
    fieldType.addEventListener('change', toggleOptionsDisplay);

    // 3. Reset Modal for Adding Field
    window.prepareAddFieldModal = function () {
        fieldForm.reset();
        editMode = false;
        editingFieldId = null;
        document.getElementById('field_id').value = '';
        document.getElementById('field_width').value = '12';
        document.getElementById('field_active').checked = true;
        document.getElementById('field_required').checked = false;
        document.getElementById('fieldModalTitle').innerHTML = '<i class="fa-solid fa-plus me-1 text-primary"></i><span>Add New Form Field</span>';
        document.getElementById('saveFieldBtn').innerHTML = '<i class="fa-solid fa-check-circle me-1"></i>Save Field';
        bootstrap.Tab.getOrCreateInstance(document.getElementById('fm-tab-basic-btn')).show();
        toggleOptionsDisplay();
    };

    // 4. Auto-generate Database Field Name
    window.autoGenerateFieldName = function (label) {
        if (!editMode) {
            const nameInput = document.getElementById('field_name');
            nameInput.value = label.toLowerCase().trim().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
        }
    };

    // 5. Edit Field Population
    document.querySelectorAll('.edit-field').forEach(function (button) {
        button.addEventListener('click', function () {
            const field = JSON.parse(this.dataset.field);
            editMode = true;
            editingFieldId = field.id;

            document.getElementById('fieldModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-1 text-primary"></i><span>Edit Form Field</span>';
            document.getElementById('saveFieldBtn').innerHTML = '<i class="fa-solid fa-check-circle me-1"></i>Update Field';

            document.getElementById('field_id').value = field.id;
            document.getElementById('field_type').value = field.type;
            document.getElementById('field_label').value = field.label ?? '';
            document.getElementById('field_name').value = field.name ?? '';
            document.getElementById('field_placeholder').value = field.placeholder ?? '';
            document.getElementById('field_default').value = field.default_value ?? '';
            document.getElementById('field_help').value = field.help_text ?? '';
            document.getElementById('field_width').value = field.column_width ?? 12;
            document.getElementById('field_min_length').value = field.min_length ?? '';
            document.getElementById('field_max_length').value = field.max_length ?? '';
            document.getElementById('field_min_value').value = field.min_value ?? '';
            document.getElementById('field_max_value').value = field.max_value ?? '';
            document.getElementById('field_required').checked = !!field.is_required;
            document.getElementById('field_active').checked = !!field.is_active;

            let optionText = '';
            if (Array.isArray(field.options)) {
                optionText = field.options.map(o => (o.label || o) + '|' + (o.value || o)).join('\n');
            }
            document.getElementById('field_options').value = optionText;

            bootstrap.Tab.getOrCreateInstance(document.getElementById('fm-tab-basic-btn')).show();
            toggleOptionsDisplay();
            fieldModal.show();
        });
    });

    // 6. Save / Update Field via AJAX
    fieldForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(fieldForm);
        formData.append('is_required', document.getElementById('field_required').checked ? 1 : 0);
        formData.append('is_active', document.getElementById('field_active').checked ? 1 : 0);

        let url = "{{ route('admin.forms.fields.store', $form) }}";
        if (editMode) {
            url = "{{ url('/admin/forms') }}/" + formId + "/fields/" + editingFieldId;
            formData.append('_method', 'PUT');
        }

        const saveBtn = document.getElementById('saveFieldBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Validation failed.');
            return data;
        })
        .then(data => {
            if (data.success) {
                fieldModal.hide();
                showStudioModalNotice('Field saved successfully.', 'Success', 'success');
                setTimeout(() => location.reload(), 800);
            }
        })
        .catch(err => {
            showStudioModalNotice(err.message, 'Validation Error', 'danger');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i>Save Field';
        });
    });

    // 7. Delete Field Handler (via Custom Modal)
    document.querySelectorAll('.delete-field').forEach(function (button) {
        button.addEventListener('click', function () {
            const fieldId = this.dataset.id;
            showStudioModalConfirm(
                'Delete Field?',
                'Are you sure you want to permanently delete this field from the form?',
                function () {
                    fetch("{{ url('/admin/forms') }}/" + formId + "/fields/" + fieldId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json'
                        },
                        body: '_method=DELETE'
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showStudioModalNotice('Field deleted successfully.', 'Deleted', 'success');
                            setTimeout(() => location.reload(), 800);
                        }
                    });
                },
                'btn-danger',
                'Yes, Delete'
            );
        });
    });

    // 8. Clone / Duplicate Field Helper
    window.cloneExistingField = function (fieldId) {
        const itemCard = document.getElementById('field-card-' + fieldId);
        if (!itemCard) return;

        fetch("{{ url('/admin/forms') }}/" + formId + "/fields/" + fieldId + "/clone", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            showStudioModalNotice('Field duplicated successfully.', 'Field Cloned', 'success');
            setTimeout(() => location.reload(), 800);
        })
        .catch(() => location.reload());
    };
});

// Copy Shortcode Helper
function copyStudioShortcode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showStudioModalNotice('Embed shortcode ' + code + ' copied to clipboard successfully!', 'Copied Successfully', 'success');
    }).catch(() => {
        showStudioModalNotice('Could not copy automatically. Please copy manually.', 'Notice', 'warning');
    });
}

// Global Media Chooser Helpers for Form Logo / Cover
function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;
    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
    if (!item || typeof item !== 'object') return null;
    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl  = item.url || item.file_path || item.path || '';
    if (!rawPath || rawPath === 'undefined') return null;
    return { path: String(rawPath).trim(), url: String(rawUrl).trim() };
}

function chooseFormEditLogoMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('edit_form_logo_media').value = picked.path;
                    const wrap = document.getElementById('edit_form_logo_preview_wrap');
                    const img  = document.getElementById('edit_form_logo_preview_img');
                    const txt  = document.getElementById('edit_form_logo_preview_txt');
                    const simWrap = document.getElementById('previewSimulatorLogoBox');
                    const simImg  = document.getElementById('previewSimulatorLogoImg');

                    if (img) img.src = picked.url;
                    if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                    if (wrap) {
                        wrap.classList.remove('d-none');
                        wrap.classList.add('d-flex');
                    }
                    if (simImg) simImg.src = picked.url;
                    if (simWrap) {
                        simWrap.classList.remove('d-none');
                        simWrap.classList.add('d-block');
                    }
                    showStudioModalNotice('Brand asset attached from Global Media Library.', 'Media Attached', 'success');
                }
            }
        });
    } else {
        showStudioModalNotice('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function clearFormEditLogoMedia() {
    document.getElementById('edit_form_logo_media').value = '';
    const wrap = document.getElementById('edit_form_logo_preview_wrap');
    const simWrap = document.getElementById('previewSimulatorLogoBox');
    if (wrap) {
        wrap.classList.add('d-none');
        wrap.classList.remove('d-flex');
    }
    if (simWrap) {
        simWrap.classList.add('d-none');
        simWrap.classList.remove('d-block');
    }
}

function zoomFormEditLogoPreview() {
    const img = document.getElementById('edit_form_logo_preview_img');
    const zoomImgTag = document.getElementById('formEditLogoZoomImgTag');
    if (img && img.src && zoomImgTag) {
        zoomImgTag.src = img.src;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('formEditLogoZoomModal')).show();
    }
}

function openStudioFullscreenPreview() {
    const renderZone = document.getElementById('fullscreenModalRenderZone');
    const liveSim = document.getElementById('liveFormFieldsSimulator');
    if (renderZone && liveSim) {
        renderZone.innerHTML = liveSim.innerHTML;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('studioFullscreenPreviewModal')).show();
    }
}

function refreshFieldListOrder() {
    location.reload();
}
</script>
@endsection