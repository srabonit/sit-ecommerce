
@extends('layouts.admin')

@section('title', 'Create Role - ' . config('app.name'))

@section('admin_content')
<style>
    /* ==========================================
       ROLE CREATE - KAIADMIN WHITE CLASSIC
    ========================================== */

    #roleCreateRoot {
        --role-primary: var(--bs-primary, #1572e8);
        --role-success: var(--bs-success, #31ce36);
        --role-heading: #2a2f5b;
        --role-text: #495057;
        --role-muted: #8d9498;
        --role-border: #ebecec;
        --role-bg: #f5f7fd;
        --role-card: #ffffff;
        --role-radius: 7px;

        width: 100%;
        min-width: 0;
        color: var(--role-text);
    }

    #roleCreateRoot *,
    #roleCreateRoot *::before,
    #roleCreateRoot *::after {
        box-sizing: border-box;
    }

    /* Page Container */
    #roleCreateRoot .role-form-container {
        padding: 18px 20px;
        max-width: 100%;
    }

    /* Page Header */
    #roleCreateRoot .role-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }

    #roleCreateRoot .role-heading-group {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    #roleCreateRoot .role-page-title {
        color: var(--role-heading);
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
        margin: 0;
    }

    #roleCreateRoot .role-page-title i {
        color: var(--role-success);
        font-size: 17px;
    }

    #roleCreateRoot .role-subtitle {
        display: block;
        margin-top: 4px;
        margin-left: 40px;
        color: var(--role-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    /* Global Element Radius */
    #roleCreateRoot .btn,
    #roleCreateRoot button,
    #roleCreateRoot .form-control,
    #roleCreateRoot .badge,
    #roleCreateRoot .input-group,
    #roleCreateRoot .input-group-text,
    #roleCreateRoot .alert,
    #roleCreateRoot .perm-item {
        border-radius: 5px !important;
    }

    #roleCreateRoot .role-info-card,
    #roleCreateRoot .perm-card,
    #roleCreateRoot .sticky-actions-bar {
        border-radius: 8px !important;
    }

    /* Buttons */
    #roleCreateRoot .role-header-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    #roleCreateRoot .role-header-actions .btn {
        min-height: 34px;
        padding: 6px 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: none;
    }

    #roleCreateRoot .btn-success {
        background-color: var(--role-success);
        border-color: var(--role-success);
    }

    #roleCreateRoot .btn-success:hover {
        filter: brightness(.94);
    }

    #roleCreateRoot .btn-outline-secondary,
    #roleCreateRoot .btn-outline-primary,
    #roleCreateRoot .btn-outline-danger {
        box-shadow: none !important;
    }

    /* Role Information Card */
    #roleCreateRoot .role-info-card {
        background: var(--role-card);
        border: 1px solid var(--role-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        padding: 18px 20px;
        margin-bottom: 16px;
    }

    #roleCreateRoot .role-section-title {
        color: var(--role-heading);
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 13px;
    }

    /* Form Controls */
    #roleCreateRoot .form-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--role-heading);
        margin-bottom: 6px;
    }

    #roleCreateRoot .form-control {
        min-height: 37px;
        font-size: 12.5px;
        border: 1px solid #e0e2e7;
        padding: 8px 11px;
        color: var(--role-text);
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #roleCreateRoot .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #roleCreateRoot .form-control:focus {
        border-color: var(--role-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #roleCreateRoot .invalid-feedback {
        font-size: 11px;
    }

    #roleCreateRoot .role-field-help {
        display: block;
        margin-top: 6px;
        color: var(--role-muted);
        font-size: 10.5px;
        line-height: 1.5;
    }

    /* Information Alert */
    #roleCreateRoot .role-info-alert {
        height: 100%;
        min-height: 65px;
        padding: 12px 13px;
        display: flex;
        align-items: flex-start;
        gap: 9px;
        background: #eaf3ff;
        border: 1px solid #d8e8ff;
        color: #3572b0;
        font-size: 11.5px;
        line-height: 1.55;
    }

    #roleCreateRoot .role-info-alert i {
        margin-top: 2px;
        flex-shrink: 0;
    }

    /* Error Alert */
    #roleCreateRoot .alert {
        border: 1px solid transparent;
        padding: 10px 13px;
        font-size: 12px;
        box-shadow: none !important;
    }

    #roleCreateRoot .alert-danger {
        border-color: #f5c2c7;
    }

    /* Sticky Toolbar */
    #roleCreateRoot .sticky-actions-bar {
        position: sticky;
        top: 64px;
        z-index: 1020;
        background: rgba(255, 255, 255, .97);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--role-border);
        padding: 11px 14px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .045);
        margin-bottom: 16px;
    }

    #roleCreateRoot .permission-search-group {
        background: #ffffff;
        border: 1px solid #e0e2e7 !important;
        box-shadow: none !important;
        min-height: 35px;
    }

    #roleCreateRoot .permission-search-group:focus-within {
        border-color: var(--role-primary) !important;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08) !important;
    }

    #roleCreateRoot .permission-search-group .input-group-text {
        padding: 0 10px;
        background: #ffffff;
        color: var(--role-muted);
    }

    #roleCreateRoot .permission-search-group .form-control {
        min-height: 34px;
        padding: 6px 8px;
        border: 0 !important;
        box-shadow: none !important;
        font-size: 11.5px;
    }

    /* Selected Counter */
    #roleCreateRoot .selected-perm-badge {
        min-height: 30px;
        padding: 5px 10px !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #edf2f7;
        color: #596579;
        border: 1px solid #e1e7ef;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    #roleCreateRoot #selectedPermCount {
        color: var(--role-primary);
        font-weight: 700 !important;
    }

    /* Toolbar Buttons */
    #roleCreateRoot .sticky-btn-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    #roleCreateRoot .sticky-btn-group .btn {
        min-height: 32px;
        padding: 5px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: none !important;
    }

    #roleCreateRoot .sticky-btn-group .btn-submit-main {
        padding-left: 13px;
        padding-right: 13px;
    }

    /* Permission Grid */
    #roleCreateRoot #modulesContainer {
        align-items: stretch;
    }

    #roleCreateRoot .module-block {
        min-width: 0;
    }

    #roleCreateRoot .perm-card {
        height: 100%;
        min-width: 0;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid var(--role-border);
        box-shadow: 0 2px 7px rgba(0, 0, 0, .025);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    #roleCreateRoot .perm-card:hover {
        border-color: #d8e1ed;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .045);
    }

    /* Module Header */
    #roleCreateRoot .perm-card-header {
        min-height: 48px;
        padding: 10px 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background: #f8f9fa;
        border-bottom: 1px solid var(--role-border);
    }

    #roleCreateRoot .module-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    #roleCreateRoot .module-icon {
        width: 27px;
        height: 27px;
        min-width: 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 11px;
    }

    #roleCreateRoot .module-name {
        color: var(--role-heading);
        font-size: 12px;
        font-weight: 600;
        margin: 0;
        overflow-wrap: anywhere;
    }

    /* Module Master Switch */
    #roleCreateRoot .module-switch {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin: 0;
        padding: 0;
    }

    #roleCreateRoot .form-check-input {
        cursor: pointer;
        box-shadow: none !important;
    }

    #roleCreateRoot .module-master-toggle {
        width: 30px;
        height: 16px;
        margin: 0 !important;
    }

    #roleCreateRoot .module-master-toggle:checked {
        background-color: var(--role-primary);
        border-color: var(--role-primary);
    }

    /* Permission List */
    #roleCreateRoot .perm-list {
        padding: 8px;
    }

    #roleCreateRoot .perm-item {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 8px 9px;
        margin-bottom: 3px;
        background: transparent;
        transition: background-color .15s ease;
    }

    #roleCreateRoot .perm-item:last-child {
        margin-bottom: 0;
    }

    #roleCreateRoot .perm-item:hover {
        background: #f5f8fc;
    }

    #roleCreateRoot .perm-check-wrap {
        flex-shrink: 0;
        padding-top: 1px;
    }

    #roleCreateRoot .perm-checkbox {
        width: 15px;
        height: 15px;
        margin: 0;
        border-radius: 4px !important;
        border-color: #cbd2dc;
        cursor: pointer;
    }

    #roleCreateRoot .perm-checkbox:checked {
        background-color: var(--role-primary);
        border-color: var(--role-primary);
    }

    #roleCreateRoot .perm-label {
        display: block;
        min-width: 0;
        width: 100%;
        cursor: pointer;
        user-select: none;
    }

    #roleCreateRoot .perm-label-title {
        display: block;
        color: var(--role-text);
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    #roleCreateRoot .perm-route {
        display: block;
        margin-top: 3px;
        color: #9aa1ac !important;
        font-family: var(--bs-font-monospace, monospace);
        font-size: 9.5px !important;
        font-weight: 400;
        line-height: 1.4;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        background: transparent !important;
        padding: 0 !important;
        border: 0 !important;
    }

    /* Hide Filtered Modules */
    #roleCreateRoot .module-block.d-none {
        display: none !important;
    }

    /* Mobile Responsive */
    @media (max-width: 767.98px) {
        #roleCreateRoot .role-form-container {
            padding: 13px 10px;
        }

        #roleCreateRoot .role-page-header {
            align-items: flex-start;
            gap: 10px;
        }

        #roleCreateRoot .role-page-title {
            font-size: 16px;
        }

        #roleCreateRoot .role-subtitle {
            margin-left: 0;
            font-size: 10.5px;
        }

        #roleCreateRoot .role-header-actions {
            width: 100%;
            display: flex;
            gap: 7px;
        }

        #roleCreateRoot .role-header-actions .btn {
            flex: 1 1 0;
            min-width: 0;
            padding: 7px 9px;
            font-size: 11px;
        }

        #roleCreateRoot .role-info-card {
            padding: 13px !important;
            margin-bottom: 13px;
        }

        #roleCreateRoot .role-info-alert {
            min-height: auto;
        }

        #roleCreateRoot .sticky-actions-bar {
            top: 56px;
            padding: 10px;
            margin-bottom: 13px;
        }

        #roleCreateRoot .toolbar-top-row {
            display: flex;
            flex-direction: column;
            align-items: stretch !important;
            gap: 9px;
        }

        #roleCreateRoot .toolbar-search {
            width: 100%;
        }

        #roleCreateRoot .toolbar-controls {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        #roleCreateRoot .sticky-btn-group {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        #roleCreateRoot .sticky-btn-group .btn {
            flex: 1 1 calc(50% - 6px);
            min-width: 0;
            min-height: 34px;
            margin: 0 !important;
            padding: 6px 5px;
            font-size: 10.5px;
        }

        #roleCreateRoot .sticky-btn-group .btn-submit-main {
            flex: 1 1 100%;
            min-height: 36px;
            font-size: 11.5px;
        }

        #roleCreateRoot #modulesContainer {
            row-gap: 12px;
        }

        #roleCreateRoot .perm-card-header {
            min-height: 45px;
            padding: 9px 11px;
        }

        #roleCreateRoot .perm-list {
            padding: 7px;
        }
    }

    @media (max-width: 380px) {
        #roleCreateRoot .role-form-container {
            padding-left: 8px;
            padding-right: 8px;
        }

        #roleCreateRoot .role-heading-group {
            gap: 6px;
        }

        #roleCreateRoot .role-page-title {
            font-size: 15px;
        }

        #roleCreateRoot .sticky-actions-bar {
            padding: 8px;
        }

        #roleCreateRoot .sticky-btn-group .btn {
            font-size: 10px;
        }
    }
</style>

<div class="container-fluid role-form-container" id="roleCreateRoot">

    <!-- Page Header -->
    <div class="role-page-header">

        <div class="flex-grow-1 min-w-0">

            <div class="role-heading-group">

                <a href="{{ route('admin.roles.index') }}"
                   class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center flex-shrink-0"
                   style="width: 32px; height: 32px; padding: 0;"
                   title="Back to Roles">

                    <i class="fas fa-arrow-left" style="font-size: 12px;"></i>

                </a>

                <h5 class="role-page-title d-flex align-items-center gap-2">
                    <i class="fas fa-shield-halved"></i>
                    <span>Create Role</span>
                </h5>

            </div>

            <small class="role-subtitle">
                Create a role and assign permissions.
            </small>

        </div>

        <div class="role-header-actions">

            <a href="{{ route('admin.roles.index') }}"
               class="btn btn-light btn-sm border text-muted">
                <i class="fas fa-xmark"></i>
                <span>Cancel</span>
            </a>

            <button type="button"
                    class="btn btn-success btn-sm"
                    onclick="document.getElementById('roleCreateForm').submit();">

                <i class="fas fa-plus"></i>
                <span>Save Role</span>

            </button>

        </div>

    </div>

    <!-- Error Alert -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 d-flex align-items-center gap-2"
             role="alert">

            <i class="fas fa-circle-exclamation text-danger flex-shrink-0"></i>

            <div class="fw-semibold">
                {{ session('error') }}
            </div>

            <button type="button"
                    class="btn-close btn-sm shadow-none ms-auto"
                    data-bs-dismiss="alert"></button>

        </div>
    @endif

    <form action="{{ route('admin.roles.store') }}" method="POST" id="roleCreateForm">
        @csrf

        <!-- Role Information -->
        <div class="role-info-card">

            <div class="role-section-title">
                <i class="fas fa-id-badge text-primary me-1"></i>
                Role Information
            </div>

            <div class="row g-3 align-items-center">

                <div class="col-12 col-md-6">

                    <label class="form-label">
                        Role Name <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           name="role_name"
                           class="form-control fw-semibold @error('role_name') is-invalid @enderror"
                           placeholder="e.g. Order Manager"
                           required
                           value="{{ old('role_name') }}"
                           autocomplete="off">

                    @error('role_name')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror

                    <small class="role-field-help">
                        A unique role slug will be created automatically. Duplicate roles are not allowed.
                    </small>

                </div>

                <div class="col-12 col-md-6">

                    <div class="role-info-alert">

                        <i class="fas fa-circle-info"></i>

                        <span>
                            Staff can only access the modules and actions selected below.
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <!-- Permission Toolbar -->
        <div class="sticky-actions-bar">

            <div class="row g-2 align-items-center toolbar-top-row">

                <!-- Permission Search -->
                <div class="col-12 col-md-4 toolbar-search">

                    <div class="input-group input-group-sm permission-search-group">

                        <span class="input-group-text border-0">
                            <i class="fas fa-search" style="font-size: 11px;"></i>
                        </span>

                        <input type="text"
                               id="permissionSearchInput"
                               class="form-control border-0 shadow-none"
                               placeholder="Search permissions..."
                               style="font-size: 12px;">

                    </div>

                </div>

                <!-- Bulk Actions -->
                <div class="col-12 col-md-8 d-flex align-items-center justify-content-between justify-content-md-end gap-2 flex-wrap toolbar-controls">

                    <span class="selected-perm-badge">

                        <i class="fas fa-check-double"></i>

                        <span id="selectedPermCount" class="bn-digit">0</span>

                        <span>Selected</span>

                    </span>

                    <div class="sticky-btn-group">

                        <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                onclick="toggleSelectAll(true)">

                            <i class="fas fa-square-check"></i>
                            <span>Select All</span>

                        </button>

                        <button type="button"
                                class="btn btn-outline-danger btn-sm"
                                onclick="toggleSelectAll(false)">

                            <i class="fas fa-square-xmark"></i>
                            <span>Deselect All</span>

                        </button>

                        <button type="submit"
                                class="btn btn-success btn-sm btn-submit-main">

                            <i class="fas fa-save"></i>
                            <span>Save Role</span>

                        </button>

                    </div>

                </div>

            </div>

        </div>

        <!-- Permission Modules -->
        <div class="row g-3" id="modulesContainer">

            @foreach($modules as $modName =>$modData)
                @php
                    $modId = Str::slug($modName, '_');
                    $modPerms =$modData['permissions'] ?? [];
                @endphp

                <div class="col-12 col-md-6 col-xl-4 module-block"
                     data-module-name="{{ strtolower($modName) }}">

                    <div class="perm-card">

                        <!-- Module Header -->
                        <div class="perm-card-header">

                            <div class="module-heading">

                                <span class="module-icon bg-{{ $modData['color'] ?? 'primary' }}-subtle text-{{$modData['color'] ?? 'primary' }}">

                                    <i class="{{ $modData['icon'] ?? 'fas fa-shield' }}"></i>

                                </span>

                                <strong class="module-name text-truncate">
                                    {{ $modName }}
                                </strong>

                            </div>

                            <div class="form-check form-switch module-switch"
                                 title="Toggle all in this module">

                                <input class="form-check-input module-master-toggle shadow-none"
                                       type="checkbox"
                                       id="toggle_mod_{{ $modId }}"
                                       data-target-module="{{ $modId }}"
                                       onchange="toggleModulePermissions(this, '{{ $modId }}')">

                            </div>

                        </div>

                        <!-- Module Permissions -->
                        <div class="perm-list" id="module_group_{{ $modId }}">

                            @foreach($modPerms as $permRoute =>$permLabel)
                                @php
                                    $checkboxId = 'perm_' . md5($permRoute);
                                @endphp

                                <div class="perm-item permission-row"
                                     data-perm-label="{{ strtolower($permLabel . ' ' .$permRoute) }}">

                                    <div class="form-check perm-check-wrap">

                                        <input class="form-check-input perm-checkbox perm-mod-{{ $modId }} shadow-none"
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permRoute }}"
                                               id="{{ $checkboxId }}"
                                               onchange="onPermissionChange('{{ $modId }}')"
                                               style="border-radius: 4px !important;">

                                    </div>

                                    <label class="perm-label"
                                           for="{{ $checkboxId }}">

                                        <span class="perm-label-title">
                                            {{ $permLabel }}
                                        </span>

                                        <code class="perm-route">
                                            {{ $permRoute }}
                                        </code>

                                    </label>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </form>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        updateSelectedCounter();

        const searchInput = document.getElementById('permissionSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                const moduleBlocks = document.querySelectorAll('.module-block');

                moduleBlocks.forEach(block => {
                    const rows = block.querySelectorAll('.permission-row');
                    let hasVisibleRow = false;

                    rows.forEach(row => {
                        const label = row.getAttribute('data-perm-label');
                        if (!query || label.includes(query)) {
                            row.classList.remove('d-none');
                            hasVisibleRow = true;
                        } else {
                            row.classList.add('d-none');
                        }
                    });

                    block.classList.toggle('d-none', !hasVisibleRow);
                });
            });
        }
    });

    function toggleSelectAll(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
        document.querySelectorAll('.module-master-toggle').forEach(mt => mt.checked = checked);
        updateSelectedCounter();
    }

    function toggleModulePermissions(masterToggle, modId) {
        const checkboxes = document.querySelectorAll('.perm-mod-' + modId);
        checkboxes.forEach(cb => cb.checked = masterToggle.checked);
        updateSelectedCounter();
    }

    function onPermissionChange(modId) {
        const checkboxes = document.querySelectorAll('.perm-mod-' + modId);
        const masterToggle = document.getElementById('toggle_mod_' + modId);

        if (masterToggle) {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            masterToggle.checked = allChecked && checkboxes.length > 0;
        }

        updateSelectedCounter();
    }

    function updateSelectedCounter() {
        const selected = document.querySelectorAll('.perm-checkbox:checked').length;
        const total = document.querySelectorAll('.perm-checkbox').length;
        const counterEl = document.getElementById('selectedPermCount');
        if (counterEl) {
            counterEl.innerText = `${selected} / ${total}`;
        }
    }
</script>
@endsection
