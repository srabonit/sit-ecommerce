
@extends('layouts.admin')

@section('title', 'Edit Role Permissions: ' . ucwords(str_replace('_', ' ', $roleSlug)) . ' - ' . config('app.name'))

@section('admin_content')

<style>
    /* =========================================
       ROLE EDIT — KAIADMIN CLASSIC
    ========================================= */

    #roleEditRoot {
        --role-primary: #1572e8;
        --role-primary-soft: #eaf2ff;
        --role-border: #e5e7eb;
        --role-text: #2a2f5b;
        --role-muted: #8d9498;
        --role-bg: #f9fafc;
        --role-radius: 5px;
        padding: 18px 20px;
        color: var(--role-text);
    }

    #roleEditRoot *,
    #roleEditRoot *::before,
    #roleEditRoot *::after {
        box-sizing: border-box;
    }

    /* Consistent Radius */
    #roleEditRoot .btn,
    #roleEditRoot button,
    #roleEditRoot .form-control,
    #roleEditRoot .badge,
    #roleEditRoot .input-group,
    #roleEditRoot .input-group-text,
    #roleEditRoot .alert,
    #roleEditRoot .perm-item,
    #roleEditRoot code {
        border-radius: 5px !important;
    }

    #roleEditRoot .perm-card,
    #roleEditRoot .sticky-actions-bar {
        border-radius: 8px !important;
    }

    /* Page Header */
    #roleEditRoot .role-page-header {
        margin-bottom: 18px;
    }

    #roleEditRoot .role-page-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin: 0;
        color: var(--role-text);
        font-size: 17px;
        font-weight: 700;
        line-height: 1.4;
    }

    #roleEditRoot .role-page-title .title-icon {
        color: var(--role-primary);
        font-size: 16px;
    }

    #roleEditRoot .role-page-subtitle {
        display: block;
        margin: 6px 0 0 38px;
        color: var(--role-muted);
        font-size: 11.5px;
        line-height: 1.6;
    }

    #roleEditRoot .role-slug {
        display: inline-block;
        padding: 3px 7px;
        margin-left: 3px;
        background: #f1f5f9;
        border: 1px solid var(--role-border);
        color: var(--role-primary);
        font-size: 10.5px;
        font-weight: 600;
        word-break: break-word;
    }

    #roleEditRoot .header-top-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
    }

    #roleEditRoot .header-top-actions .btn {
        min-height: 33px;
        padding: 7px 13px;
        font-size: 11.5px;
        font-weight: 600;
    }

    #roleEditRoot .back-btn {
        width: 31px;
        height: 31px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Alerts */
    #roleEditRoot .alert {
        padding: 10px 13px;
        font-size: 12px;
    }

    /* Sticky Toolbar */
    #roleEditRoot .sticky-actions-bar {
        position: sticky;
        top: 64px;
        z-index: 1020;
        padding: 12px 14px;
        margin-bottom: 17px;
        background: rgba(255, 255, 255, .97);
        border: 1px solid var(--role-border);
        box-shadow: 0 3px 12px rgba(0, 0, 0, .045);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    #roleEditRoot .search-group {
        min-height: 34px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--role-border);
        border-radius: 5px;
    }

    #roleEditRoot .search-group .input-group-text {
        padding: 0 10px;
        background: #fff;
        border: 0;
        color: #9ba2aa;
        font-size: 11px;
    }

    #roleEditRoot .search-group .form-control {
        min-height: 33px;
        padding: 6px 9px;
        border: 0;
        outline: 0;
        box-shadow: none;
        color: var(--role-text);
        font-size: 11.5px;
    }

    #roleEditRoot .search-group .form-control:focus {
        box-shadow: none;
    }

    #roleEditRoot .search-group:focus-within {
        border-color: var(--role-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .08);
    }

    #roleEditRoot .selected-counter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 30px;
        padding: 5px 10px;
        background: var(--role-primary-soft);
        border: 1px solid #d7e6ff;
        color: var(--role-primary);
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    #roleEditRoot .selected-counter i {
        font-size: 11px;
    }

    #roleEditRoot #selectedPermCount {
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    #roleEditRoot .sticky-btn-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    #roleEditRoot .sticky-btn-group .btn {
        min-height: 31px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    #roleEditRoot .sticky-btn-group .btn i {
        margin-right: 5px;
    }

    /* Module Cards */
    #roleEditRoot .perm-card {
        height: 100%;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--role-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .025);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    #roleEditRoot .perm-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .045);
    }

    #roleEditRoot .perm-card-header {
        min-height: 48px;
        padding: 9px 12px;
        background: #fbfcfe;
        border-bottom: 1px solid var(--role-border);
    }

    #roleEditRoot .module-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        border-radius: 5px;
        flex-shrink: 0;
        font-size: 11px;
    }

    #roleEditRoot .module-name {
        color: var(--role-text);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    /* Module Switch */
    #roleEditRoot .module-master-toggle {
        width: 31px;
        height: 17px;
        margin: 0;
        cursor: pointer;
        border-color: #cbd5e1;
        box-shadow: none !important;
    }

    #roleEditRoot .module-master-toggle:checked {
        background-color: var(--role-primary);
        border-color: var(--role-primary);
    }

    /* Permission Rows */
    #roleEditRoot .perm-list {
        padding: 7px;
    }

    #roleEditRoot .perm-item {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 8px 9px;
        margin-bottom: 3px;
        background: #fff;
        border: 1px solid transparent;
        transition: background .15s ease, border-color .15s ease;
    }

    #roleEditRoot .perm-item:last-child {
        margin-bottom: 0;
    }

    #roleEditRoot .perm-item:hover {
        background: #f8faff;
        border-color: #edf2f8;
    }

    #roleEditRoot .perm-item .form-check {
        padding: 0;
        margin: 1px 0 0;
        flex-shrink: 0;
    }

    #roleEditRoot .perm-checkbox {
        width: 15px;
        height: 15px;
        margin: 0;
        cursor: pointer;
        border-color: #cbd5e1;
        box-shadow: none !important;
    }

    #roleEditRoot .perm-checkbox:checked {
        background-color: var(--role-primary);
        border-color: var(--role-primary);
    }

    #roleEditRoot .permission-label {
        min-width: 0;
        width: 100%;
        cursor: pointer;
        overflow-wrap: anywhere;
    }

    #roleEditRoot .permission-title {
        display: block;
        color: #3b405c;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.45;
    }

    #roleEditRoot .permission-route {
        display: block;
        margin-top: 3px;
        padding: 0;
        background: transparent;
        border: 0;
        color: #9aa1ac;
        font-size: 9.5px;
        font-weight: 500;
        line-height: 1.4;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    /* Empty Search State */
    #roleEditRoot .module-block.d-none {
        display: none !important;
    }

    /* Form Controls */
    #roleEditRoot .form-control:focus {
        border-color: var(--role-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .10);
    }

    #roleEditRoot .btn {
        transition: all .15s ease;
    }

    #roleEditRoot .btn-primary {
        background-color: var(--role-primary);
        border-color: var(--role-primary);
    }

    #roleEditRoot .btn-primary:hover {
        background-color: #1265cf;
        border-color: #1265cf;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #roleEditRoot {
            padding: 15px;
        }

        #roleEditRoot .sticky-actions-bar {
            top: 60px;
        }
    }

    @media (max-width: 767.98px) {
        #roleEditRoot {
            padding: 12px 10px;
        }

        #roleEditRoot .role-page-header {
            align-items: flex-start !important;
        }

        #roleEditRoot .role-page-title {
            font-size: 15px;
        }

        #roleEditRoot .role-page-subtitle {
            margin-left: 0;
            font-size: 10.5px;
        }

        #roleEditRoot .header-top-actions {
            display: flex;
            width: 100%;
        }

        #roleEditRoot .header-top-actions .btn {
            flex: 1 1 0;
            justify-content: center;
            text-align: center;
        }

        #roleEditRoot .sticky-actions-bar {
            top: 55px;
            padding: 10px;
            margin-bottom: 13px;
        }

        #roleEditRoot .toolbar-actions {
            justify-content: space-between;
        }

        #roleEditRoot .sticky-btn-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        #roleEditRoot .sticky-btn-group .btn {
            width: 100%;
            justify-content: center;
            margin: 0 !important;
            padding: 7px 5px;
            font-size: 10.5px;
        }

        #roleEditRoot .sticky-btn-group .btn-submit-main {
            grid-column: 1 / -1;
        }

        #roleEditRoot .perm-card-header {
            min-height: 45px;
        }
    }

    @media (max-width: 420px) {
        #roleEditRoot .role-page-title {
            font-size: 14px;
        }

        #roleEditRoot .selected-counter {
            font-size: 10px;
        }

        #roleEditRoot .sticky-actions-bar {
            padding: 8px;
        }

        #roleEditRoot .perm-item {
            padding: 7px;
        }
    }
</style>

<div class="container-fluid" id="roleEditRoot">

    <!-- Page Header -->
    <div class="role-page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">

        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.roles.index') }}"
                   class="btn btn-outline-secondary back-btn shadow-sm"
                   title="Back to Roles"
                   aria-label="Back to Roles">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <h5 class="role-page-title">
                    <i class="fas fa-user-shield title-icon"></i>
                    <span>Edit Role:</span>
                    <span class="text-primary">{{ ucwords(str_replace('_', ' ', $roleSlug)) }}</span>
                </h5>
            </div>

            <small class="role-page-subtitle">
                Manage role permissions
                <code class="role-slug">{{ $roleSlug }}</code>
            </small>
        </div>

        <div class="header-top-actions">
            <a href="{{ route('admin.roles.index') }}"
               class="btn btn-light border text-secondary shadow-sm">
                Cancel
            </a>

            <button type="button"
                    class="btn btn-primary shadow-sm"
                    onclick="document.getElementById('rolePermForm').submit();">
                <i class="fas fa-floppy-disk me-1"></i>
                Save Changes
            </button>
        </div>
    </div>

    <!-- Error Alert -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm d-flex align-items-center gap-2"
             role="alert">
            <i class="fas fa-circle-exclamation flex-shrink-0"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button"
                    class="btn-close shadow-none ms-auto"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.roles.store') }}" method="POST" id="rolePermForm">
        @csrf

        <input type="hidden" name="role_name" value="{{ $roleSlug }}">
        <input type="hidden" name="is_edit" value="1">
        <input type="hidden" name="original_role" value="{{ $roleName ?? $roleSlug }}">

        <!-- Sticky Permission Toolbar -->
        <div class="sticky-actions-bar">
            <div class="row g-2 align-items-center">

                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm search-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               id="permissionSearchInput"
                               class="form-control"
                               placeholder="Search permissions...">
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-2 flex-wrap toolbar-actions">

                        <span class="selected-counter">
                            <i class="fas fa-check-double"></i>
                            <span id="selectedPermCount">0</span>
                            <span>Selected</span>
                        </span>

                        <div class="sticky-btn-group">
                            <button type="button"
                                    class="btn btn-outline-primary"
                                    onclick="toggleSelectAll(true)">
                                <i class="fas fa-square-check"></i>
                                Select All
                            </button>

                            <button type="button"
                                    class="btn btn-outline-danger"
                                    onclick="toggleSelectAll(false)">
                                <i class="fas fa-square-xmark"></i>
                                Deselect All
                            </button>

                            <button type="submit"
                                    class="btn btn-primary btn-submit-main">
                                <i class="fas fa-check"></i>
                                Save & Apply
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Permission Modules -->
        <div class="row g-3" id="modulesContainer">

            @foreach($modules as $modName =>$modData)

                @php
                    $modId = Str::slug($modName, '_');$modPerms = $modData['permissions'] ?? [];$assignedInMod = 0;
                    foreach(array_keys($modPerms) as$rKey) {
                        if($assignedPermissions->contains($rKey))$assignedInMod++;
                    }
                    $isAllModChecked = (count($modPerms) > 0 && $assignedInMod === count($modPerms));
                @endphp

                <div class="col-12 col-md-6 col-xl-4 module-block"
                     data-module-name="{{ strtolower($modName) }}">

                    <div class="perm-card">

                        <!-- Module Header -->
                        <div class="perm-card-header d-flex align-items-center justify-content-between gap-2">

                            <div class="d-flex align-items-center gap-2 min-w-0">

                                <span class="module-icon bg-{{ $modData['color'] ?? 'primary' }}-subtle text-{{$modData['color'] ?? 'primary' }}">
                                    <i class="{{ $modData['icon'] ?? 'fas fa-shield' }}"></i>
                                </span>

                                <strong class="module-name">
                                    {{ $modName }}
                                </strong>
                            </div>

                            <div class="form-check form-switch m-0 ps-0 flex-shrink-0"
                                 title="Toggle all in this module">

                                <input class="form-check-input module-master-toggle shadow-none"
                                       type="checkbox"
                                       id="toggle_mod_{{ $modId }}"
                                       data-target-module="{{ $modId }}"
                                       {{ $isAllModChecked ? 'checked' : '' }}
                                       onchange="toggleModulePermissions(this, '{{ $modId }}')">
                            </div>
                        </div>

                        <!-- Permission List -->
                        <div class="perm-list" id="module_group_{{ $modId }}">

                            @foreach($modPerms as $permRoute =>$permLabel)

                                @php
                                    $isChecked = $assignedPermissions->contains($permRoute);
                                    $checkboxId = 'perm_' . md5($permRoute);
                                @endphp

                                <div class="perm-item permission-row"
                                     data-perm-label="{{ strtolower($permLabel . ' ' .$permRoute) }}">

                                    <div class="form-check">
                                        <input class="form-check-input perm-checkbox perm-mod-{{ $modId }} shadow-none"
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permRoute }}"
                                               id="{{ $checkboxId }}"
                                               {{ $isChecked ? 'checked' : '' }}
                                               onchange="onPermissionChange('{{ $modId }}')">
                                    </div>

                                    <label class="permission-label"
                                           for="{{ $checkboxId }}">

                                        <span class="permission-title">
                                            {{ $permLabel }}
                                        </span>

                                        <code class="permission-route">
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
        syncAllModuleSwitches();

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

    function syncAllModuleSwitches() {
        document.querySelectorAll('.module-master-toggle').forEach(masterToggle => {
            const modId = masterToggle.getAttribute('data-target-module');
            const checkboxes = document.querySelectorAll('.perm-mod-' + modId);

            if (checkboxes.length > 0) {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                masterToggle.checked = allChecked;
            }
        });
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