@extends('layouts.admin')

@section('title', 'Create Form - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #formCreateRoot .btn,
    #formCreateRoot .btn-sm,
    #formCreateRoot button,
    #formCreateRoot .form-control,
    #formCreateRoot .form-select,
    #formCreateRoot .badge,
    #formCreateRoot .card,
    #formCreateRoot .builder-title-card,
    #formCreateRoot .builder-card,
    #formCreateRoot .proof-media-surface,
    #formCreateRoot .proof-thumb-box,
    .fb-create-modal .modal-content,
    .fb-create-modal .btn {
        border-radius: 5px !important;
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

    .form-create-wrap {
        max-width: 1000px;
        margin: auto;
    }

    .builder-title-card {
        background: #0f172a;
        color: #ffffff;
        padding: 16px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 16px;
    }

    .builder-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 18px 20px;
    }

    .form-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    #formCreateRoot .form-control,
    #formCreateRoot .form-select {
        min-height: 34px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #formCreateRoot .form-control:focus,
    #formCreateRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    textarea.form-control {
        min-height: 80px;
    }

    .form-check-input {
        width: 16px;
        height: 16px;
        cursor: pointer;
        border-radius: 4px !important;
    }
    .form-check-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
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

    @media (max-width: 767.98px) {
        .builder-card {
            padding: 14px 16px !important;
        }
        .form-check-group {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formCreateRoot">
    <div class="form-create-wrap">

        <!-- Header Card -->
        <div class="builder-title-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div>
                <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fa-solid fa-file-circle-plus text-primary"></i>
                    <span>Create New Form</span>
                </h5>
                <small class="text-white-50 d-block" style="font-size: 12px; line-height: 1.35;">
                    Set up form identification, email notification rules &amp; attach branding before configuring fields.
                </small>
            </div>
            <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact text-white border-white-50 shadow-xs">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Directory
            </a>
        </div>

        <form method="POST" action="{{ route('admin.forms.store') }}" id="formCreateEntityForm">
            @csrf
            <!-- Hidden input for Form Logo / Cover Banner via Global Media Chooser -->
            <input type="hidden" name="logo" id="create_form_logo_media" value="{{ old('logo') }}">

            <div class="builder-card">
                <div class="row g-3">
                    <!-- Form Name -->
                    <div class="col-12 col-md-8">
                        <label class="form-label">Form Title / Display Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Contact Us / Wholesale Inquiry" required>
                        @error('name')
                            <div class="text-danger small mt-1 font-monospace" style="font-size: 10.5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div class="col-12 col-md-4">
                        <label class="form-label">Form URL Slug</label>
                        <input type="text" name="slug" class="form-control font-monospace" value="{{ old('slug') }}" placeholder="contact-us">
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description / Instructions</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief note explaining the purpose of this form to users...">{{ old('description') }}</textarea>
                    </div>

                    <!-- 200% Global Media Chooser Component: Form Brand Logo / Header Banner -->
                    <div class="col-12">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-paperclip text-primary me-1"></i> Form Header Logo / Banner (Global Media):</span>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">MEDIA LIBRARY</span>
                        </label>
                        <div class="proof-media-surface">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseFormLogoMedia()">
                                    <i class="fa-solid fa-photo-film me-1"></i>
                                    <span>Choose from Media Library</span>
                                </button>
                                <span class="small text-muted" style="font-size: 10.5px;">Attach branded header banner or logo</span>
                            </div>

                            <div id="form_logo_preview_wrap" class="mt-2 {{ old('logo') ? 'd-flex' : 'd-none' }} align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                <div class="proof-thumb-box shadow-xs" onclick="zoomFormLogoPreview()" title="Click to Zoom Logo">
                                    <img id="form_logo_preview_img" src="{{ old('logo') }}" alt="Logo Preview">
                                    <div class="proof-thumb-hover-overlay">
                                        <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9px;">
                                        <i class="fas fa-check-circle me-1"></i>Logo Attached
                                    </span>
                                    <small class="text-muted d-block text-truncate font-monospace" id="form_logo_preview_txt" style="font-size: 9.5px;">{{ old('logo') }}</small>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2" style="font-size: 9.5px !important;" onclick="clearFormLogoMedia()">
                                    <i class="fa-solid fa-trash-can me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Email -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Notification Admin Email</label>
                        <input type="email" name="admin_email" class="form-control font-monospace" value="{{ old('admin_email', 'info.srabonit@gmail.com') }}" placeholder="admin@example.com">
                    </div>

                    <!-- Email Subject -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Submission Email Subject</label>
                        <input type="text" name="email_subject" class="form-control" value="{{ old('email_subject', 'New Form Submission') }}" placeholder="New Contact Form Submission">
                    </div>

                    <!-- Success Message -->
                    <div class="col-12">
                        <label class="form-label">Success Message</label>
                        <input type="text" name="success_message" class="form-control" value="{{ old('success_message', 'Your message has been sent successfully.') }}">
                    </div>

                    <!-- Redirect URL -->
                    <div class="col-12">
                        <label class="form-label">Redirect URL (Optional)</label>
                        <input type="text" name="redirect_url" class="form-control font-monospace" value="{{ old('redirect_url') }}" placeholder="/thank-you">
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                            Leave empty if you want to display the success message inline on the same page.
                        </small>
                    </div>

                    <!-- Behavior Toggles -->
                    <div class="col-12">
                        <label class="form-label mb-2">Form Activation &amp; Email Rules</label>
                        <div class="d-flex flex-wrap gap-3 form-check-group p-2 border bg-light" style="border-radius: 5px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="chk_active" value="1" checked>
                                <label class="form-check-label" for="chk_active">Active Status</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="save_submissions" id="chk_save_subs" value="1" checked>
                                <label class="form-check-label" for="chk_save_subs">Save Submissions</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_admin_email" id="chk_send_admin" value="1" checked>
                                <label class="form-check-label" for="chk_send_admin">Send Admin Email</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_user_email" id="chk_send_user" value="1">
                                <label class="form-check-label" for="chk_send_user">Send User Email</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top mt-3 pt-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.forms.index') }}" class="btn btn-light btn-compact border shadow-xs">
                        <i class="fa-solid fa-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                        <i class="fa-solid fa-check-circle me-1"></i> Create Form &amp; Setup Fields
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Lightbox Zoom Modal for Form Logo -->
<div class="modal fade fb-create-modal" id="formLogoZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fa-solid fa-image text-primary me-1"></i> Form Header Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="formLogoZoomImgTag" src="" alt="Logo Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return { path: String(rawPath).trim(), url: String(rawUrl).trim() };
    }

    function chooseFormLogoMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('create_form_logo_media').value = picked.path;
                        const wrap = document.getElementById('form_logo_preview_wrap');
                        const img  = document.getElementById('form_logo_preview_img');
                        const txt  = document.getElementById('form_logo_preview_txt');
                        if (img) img.src = picked.url;
                        if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                        if (wrap) {
                            wrap.classList.remove('d-none');
                            wrap.classList.add('d-flex');
                        }
                    }
                }
            });
        } else {
            alert('Global Media Library is loading, please try again.');
        }
    }

    function clearFormLogoMedia() {
        document.getElementById('create_form_logo_media').value = '';
        const wrap = document.getElementById('form_logo_preview_wrap');
        if (wrap) {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }
    }

    function zoomFormLogoPreview() {
        const img = document.getElementById('form_logo_preview_img');
        const zoomImgTag = document.getElementById('formLogoZoomImgTag');
        if (img && img.src && zoomImgTag) {
            zoomImgTag.src = img.src;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('formLogoZoomModal')).show();
        }
    }
</script>
@endsection