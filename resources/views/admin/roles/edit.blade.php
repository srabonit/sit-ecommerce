@extends('layouts.admin')

@section('title', 'Edit Role Permissions: ' . ucwords(str_replace('_', ' ', $roleSlug)) . ' - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #roleEditRoot .btn,
    #roleEditRoot .btn-sm,
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

    /* Page Container */
    .role-form-container {
        padding: 18px 20px;
    }

    /* Sticky Controls Bar */
    .sticky-actions-bar {
        position: sticky;
        top: 64px;
        z-index: 1020;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        margin-bottom: 18px;
    }

    #roleEditRoot .form-control {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 7px 12px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #roleEditRoot .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Permission Module Cards */
    .perm-card {
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }
    .perm-card:hover {
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
        border-color: #cbd5e1;
    }
    .perm-card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 11px 14px;
        border-top-left-radius: 7px;
        border-top-right-radius: 7px;
    }
    .perm-item {
        padding: 7px 10px;
        transition: background-color 0.15s ease;
        margin-bottom: 2px;
    }
    .perm-item:last-child {
        margin-bottom: 0;
    }
    .perm-item:hover {
        background-color: #f8fafc;
    }

    .form-check-input {
        cursor: pointer;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .role-form-container {
            padding: 12px 10px;
        }
        .header-top-actions {
            width: 100%;
            display: flex;
            gap: 8px;
        }
        .header-top-actions .btn {
            flex: 1 1 0;
            justify-content: center;
            text-align: center;
            padding: 7px 10px;
            font-size: 12px;
        }
        .header-subtitle-text {
            margin-left: 0 !important;
            padding-left: 0 !important;
            margin-top: 4px;
        }
        .sticky-actions-bar {
            top: 56px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }
        .sticky-btn-group {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .sticky-btn-group .btn {
            flex: 1 1 calc(50% - 6px);
            justify-content: center;
            text-align: center;
            font-size: 11.5px;
            padding: 7px 8px;
        }
        .sticky-btn-group .btn-submit-main {
            flex: 1 1 100%;
            padding: 8px 12px;
            font-size: 12.5px;
        }
    }
</style>

<div class="container-fluid role-form-container" id="roleEditRoot">
    <!-- Top Header & Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm p-0 d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 30px; height: 30px;" title="Back to Roles">
                    <i class="fas fa-arrow-left" style="font-size: 11px;"></i>
                </a>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center flex-wrap" style="font-size: 17px;">
                    <i class="fas fa-user-shield text-primary me-2"></i>
                    <span>Edit Role Permissions:</span>
                    <span class="text-primary ms-1.5">{{ ucwords(str_replace('_', ' ', $roleSlug)) }}</span>
                </h5>
            </div>
            <small class="text-muted d-block ms-sm-4 ps-sm-2 mb-3 header-subtitle-text" style="font-size: 11.5px;">
                Configure module-level and action-level authorization for role: <code class="font-monospace text-primary bg-light px-2 py-1 border">{{ $roleSlug }}</code>
            </small>
        </div>
        <div class="header-top-actions d-flex gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-light btn-sm px-3 py-1.5 border text-muted fw-semibold d-inline-flex align-items-center shadow-xs">
                <span>Cancel</span>
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3.5 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center" onclick="document.getElementById('rolePermForm').submit();">
                <i class="fas fa-floppy-disk me-2"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </div>

    <!-- Error Alert -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-circle-exclamation fs-6 text-danger flex-shrink-0 me-1"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.roles.store') }}" method="POST" id="rolePermForm">
        @csrf
        <input type="hidden" name="role_name" value="{{ $roleSlug }}">
        <input type="hidden" name="is_edit" value="1">
        <input type="hidden" name="original_role" value="{{ $roleName ?? $roleSlug }}">

        <!-- Sticky Controls Bar -->
        <div class="sticky-actions-bar">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm overflow-hidden border shadow-xs">
                        <span class="input-group-text bg-white border-0 text-muted ps-2.5"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" id="permissionSearchInput" class="form-control border-0 py-1.5 shadow-none" placeholder="Filter permissions in real-time..." style="font-size: 12px;">
                    </div>
                </div>

                <div class="col-12 col-md-8 d-flex align-items-center justify-content-between justify-content-md-end gap-2 flex-wrap">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1.5 small d-inline-flex align-items-center fw-semibold" style="font-size: 11px;">
                        <i class="fas fa-check-double me-2"></i>
                        <span id="selectedPermCount" class="bn-digit me-1">0</span>
                        <span>Selected</span>
                    </span>

                    <div class="sticky-btn-group d-flex gap-1.5">
                        <button type="button" class="btn me-2 btn-outline-primary btn-sm px-2.5 py-1 fw-semibold d-inline-flex align-items-center shadow-xs" style="font-size: 11.5px;" onclick="toggleSelectAll(true)">
                            <i class="fas fa-square-check me-2"></i>
                            <span>Select All</span>
                        </button>
                        <button type="button" class="btn me-2 btn-outline-danger btn-sm px-2.5 py-1 fw-semibold d-inline-flex align-items-center shadow-xs" style="font-size: 11.5px;" onclick="toggleSelectAll(false)">
                            <i class="fas fa-square-xmark me-2"></i>
                            <span>Deselect All</span>
                        </button>
                        <button type="submit" class="btn me-2 btn-primary btn-sm px-3.5 py-1 fw-semibold shadow-xs d-inline-flex align-items-center btn-submit-main" style="font-size: 11.5px;">
                            <i class="fas fa-check me-2"></i>
                            <span>Save & Apply</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules Grid -->
        <div class="row g-3" id="modulesContainer">
            @foreach($modules as $modName =>$modData)
                @php
                    $modId = Str::slug($modName, '_');$modPerms = $modData['permissions'] ?? [];$assignedInMod = 0;
                    foreach(array_keys($modPerms) as$rKey) {
                        if($assignedPermissions->contains($rKey))$assignedInMod++;
                    }
                    $isAllModChecked = (count($modPerms) > 0 && $assignedInMod === count($modPerms));
                @endphp
                <div class="col-12 col-md-6 col-xl-4 module-block" data-module-name="{{ strtolower($modName) }}">
                    <div class="perm-card h-100 overflow-hidden">
                        <div class="perm-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="badge bg-{{ $modData['color'] ?? 'primary' }}-subtle text-{{$modData['color'] ?? 'primary' }} p-1.5 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px;">
                                    <i class="{{ $modData['icon'] ?? 'fas fa-shield' }}" style="font-size: 11px;"></i>
                                </span>
                                <strong class="text-dark small mb-0 text-truncate" style="font-size: 12.5px;">{{ $modName }}</strong>
                            </div>
                            <div class="form-check form-switch m-0 ps-0" title="Toggle all in this module">
                                <input class="form-check-input module-master-toggle shadow-none m-0" type="checkbox" id="toggle_mod_{{ $modId }}"
                                       data-target-module="{{ $modId }}" {{ $isAllModChecked ? 'checked' : '' }}
                                       onchange="toggleModulePermissions(this, '{{ $modId }}')">
                            </div>
                        </div>

                        <div class="p-2" id="module_group_{{ $modId }}">
                            @foreach($modPerms as $permRoute =>$permLabel)
                                @php
                                    $isChecked = $assignedPermissions->contains($permRoute);
                                    $checkboxId = 'perm_' . md5($permRoute);
                                @endphp
                                <div class="perm-item d-flex align-items-start gap-2 permission-row" data-perm-label="{{ strtolower($permLabel . ' ' .$permRoute) }}">
                                    <div class="form-check m-0 pt-0.5">
                                        <input class="form-check-input perm-checkbox perm-mod-{{ $modId }} shadow-none" type="checkbox"
                                               name="permissions[]" value="{{ $permRoute }}" id="{{ $checkboxId }}"
                                               {{ $isChecked ? 'checked' : '' }} onchange="onPermissionChange('{{ $modId }}')" style="border-radius: 4px !important;">
                                    </div>
                                    <label class="form-check-label text-dark small user-select-none w-100" for="{{ $checkboxId }}" style="cursor: pointer;">
                                        <span class="d-block fw-semibold text-dark" style="font-size: 12px; line-height: 1.3;">{{ $permLabel }}</span>
                                        <code class="text-muted font-monospace d-block mt-0.5" style="font-size: 10px;">{{ $permRoute }}</code>
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