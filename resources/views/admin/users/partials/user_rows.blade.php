
@forelse($users as $index => $u)
    @php
        $isSelf = ($u->id == auth()->id());
        $isSuper = (!empty($u->is_super_admin) || strtolower($u->role) === 'super_admin');
        $isCustomer = ($u->role === 'customer');
        $statusStr = strtolower($u->status ?? ($u->is_active ? 'active' : 'inactive'));

        // Hide lock, status, and trash actions for own authenticated session
        $canManage = !$isSelf && (auth()->user()->isSuperAdmin() || !$isSuper);

        $resolvePhoto = function ($path) {
            if (empty($path))
                return null;
            if (filter_var($path, FILTER_VALIDATE_URL))
                return $path;
            $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
            if (file_exists(public_path($clean)))
                return asset($clean);
            if (file_exists(public_path('uploads/' . $clean)))
                return asset('uploads/' . $clean);
            return asset($clean);
        };

        $avatarPhoto = $resolvePhoto($u->photo ?? null);

        $rawRole = trim((string) ($u->role ?? ''));
        $cleanRole = str_replace(['_', '-'], ' ', strtolower($rawRole));
    @endphp

    <tr id="user-row-{{ $u->id }}" class="align-middle user-management-row">

        {{-- Bulk Selection Checkbox --}}
        <td class="text-center py-3 px-2">
            @if(!$isSelf && (!$isSuper || auth()->user()->isSuperAdmin()))
                <input
                    type="checkbox"
                    class="form-check-input user-select-checkbox shadow-none mt-0"
                    value="{{ $u->id }}"
                    onchange="handleUserSelectionChange()"
                    style="border-radius: 4px; cursor: pointer;"
                >
            @else
                <span
                    class="text-muted opacity-50"
                    title="Protected System Account"
                >
                    <i class="fas fa-lock fa-xs"></i>
                </span>
            @endif
        </td>

        {{-- Serial Index --}}
        <td class="font-monospace text-muted fw-semibold py-3 px-2 bn-digit user-serial-cell">
            {{ $users->firstItem() + $index }}
        </td>

        {{-- Staff / User Identity --}}
        <td class="py-3 px-3 user-identity-cell">
            <div class="d-flex align-items-center gap-2">

                <div class="user-avatar rounded-circle bg-light border text-secondary d-flex align-items-center justify-content-center fw-bold overflow-hidden flex-shrink-0">
                    @if(!empty($avatarPhoto))
                        <img
                            src="{{ $avatarPhoto }}"
                            alt="{{ $u->name }}"
                            class="w-100 h-100 object-fit-cover"
                            onerror="this.style.display='none'; this.parentElement.innerText='{{ strtoupper(substr($u->name, 0, 1)) }}';"
                        >
                    @else
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    @endif
                </div>

                <div class="min-w-0 pe-1">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <strong class="text-dark text-truncate user-name">
                            {{ $u->name }}
                        </strong>

                        @if($isSuper)
                            <span
                                class="badge bg-danger rounded-circle d-inline-flex align-items-center justify-content-center user-crown-badge"
                                title="Super Admin"
                            >
                                <i class="fas fa-crown text-white"></i>
                            </span>
                        @endif

                        @if($isSelf)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle user-you-badge">
                                You
                            </span>
                        @endif
                    </div>

                    <small class="text-muted d-block text-truncate user-designation">
                        {{ $u->designation ?: ($isCustomer ? 'Registered Customer' : 'Staff Member') }}
                    </small>
                </div>
            </div>
        </td>

        {{-- Role, Branch & Creator Metadata --}}
        <td class="py-3 px-3 user-role-cell">

            <div class="mb-1">
                @if($isCustomer)
                    <span class="badge bg-info-subtle text-info border border-info-subtle user-role-badge">
                        Customer
                    </span>
                @elseif($isSuper)
                    <span class="badge bg-danger text-white user-role-badge">
                        Super Admin
                    </span>
                @elseif(!empty($rawRole))
                    <span class="badge badge-soft-primary user-role-badge">
                        {{ ucwords($cleanRole) }}
                    </span>
                @else
                    <span class="badge badge-soft-secondary user-role-badge">
                        Staff
                    </span>
                @endif
            </div>

            @if(!empty($u->branch_name))
                <small class="d-block text-secondary text-truncate user-branch">
                    <i class="fas fa-code-branch me-1 opacity-75"></i>
                    {{ $u->branch_name }}
                </small>
            @endif

            @if(!empty($u->creator_name))
                <small class="text-muted d-block text-truncate user-creator">
                    By: <strong>{{ $u->creator_name }}</strong>
                </small>
            @else
                <small class="text-muted d-block user-creator">
                    <em>Self-Registered</em>
                </small>
            @endif
        </td>

        {{-- Contact Channels --}}
        <td class="py-3 px-3 user-contact-cell">
            <div class="font-monospace text-dark fw-semibold user-phone bn-digit">
                <i class="fas fa-phone text-muted me-1"></i>
                {{ $u->phone ?: 'No Phone' }}
            </div>

            <small class="text-muted text-break d-block font-monospace user-email">
                <i class="far fa-envelope me-1"></i>
                {{ $u->email }}
            </small>
        </td>

        {{-- Access & Security Status --}}
        <td class="text-center py-3 px-2 text-nowrap">
            @if(!empty($u->is_locked))
                <span
                    class="badge badge-soft-danger user-status-badge"
                    title="{{ $u->locked_reason ?: 'Account Locked' }}"
                >
                    <i class="fas fa-lock me-1"></i>
                    Locked
                </span>
            @elseif($statusStr === 'active')
                <span class="badge badge-soft-success user-status-badge">
                    <i class="fas fa-circle-check me-1"></i>
                    Active
                </span>
            @elseif($statusStr === 'suspended')
                <span class="badge badge-soft-warning user-status-badge">
                    <i class="fas fa-triangle-exclamation me-1"></i>
                    Suspended
                </span>
            @elseif($statusStr === 'frozen')
                <span class="badge badge-soft-info user-status-badge">
                    <i class="fas fa-snowflake me-1"></i>
                    Frozen
                </span>
            @elseif($statusStr === 'blocked')
                <span class="badge badge-soft-danger user-status-badge">
                    <i class="fas fa-ban me-1"></i>
                    Blocked
                </span>
            @else
                <span class="badge badge-soft-secondary user-status-badge">
                    <i class="fas fa-power-off me-1"></i>
                    Inactive
                </span>
            @endif
        </td>

        {{-- Activity Timestamps --}}
        <td class="py-3 px-3 user-activity-cell">
            <small class="text-dark d-block font-monospace user-created-date bn-digit">
                <i class="far fa-calendar-plus text-muted me-1"></i>
                {{ date('d M Y', strtotime($u->created_at)) }}
            </small>

            @if(!empty($u->last_login_at))
                <small class="text-secondary d-block font-monospace user-last-login bn-digit">
                    <i class="fas fa-right-to-bracket text-muted me-1"></i>
                    {{ date('d M, h:i A', strtotime($u->last_login_at)) }}
                </small>
            @else
                <small class="text-muted d-block font-monospace user-last-login">
                    <i class="far fa-clock me-1"></i>
                    Never Logged In
                </small>
            @endif
        </td>

        {{-- Action Dropdown Menu --}}
        <td class="text-end text-nowrap py-3 px-2 user-action-cell">
            <div class="dropdown d-inline-block">

                <button
                    class="btn btn-light btn-sm border dropdown-toggle user-action-toggle shadow-none"
                    type="button"
                    data-bs-toggle="dropdown"
                    data-bs-boundary="viewport"
                    data-bs-display="dynamic"
                    aria-expanded="false"
                    title="More Actions"
                >
                    <i class="fas fa-ellipsis-vertical"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border p-1 user-action-dropdown">

                    <li>
                        <a
                            class="dropdown-item d-flex align-items-center gap-2"
                            href="{{ route('admin.roles.users.show', $u->id) }}"
                        >
                            <i class="fas fa-eye text-primary user-action-icon"></i>
                            <span>View Profile</span>
                        </a>
                    </li>

                    @if(!$isCustomer)
                        <li>
                            <a
                                class="dropdown-item d-flex align-items-center gap-2"
                                href="{{ route('admin.roles.users.edit_staff', $u->id) }}"
                            >
                                <i class="fas fa-user-pen text-secondary user-action-icon"></i>
                                <span>Edit Staff</span>
                            </a>
                        </li>
                    @endif

                    @if($isSelf)
                        <li>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2"
                                onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')"
                            >
                                <i class="fas fa-key text-warning user-action-icon"></i>
                                <span>Reset My Password</span>
                            </button>
                        </li>
                    @endif

                    @if($canManage)
                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>

                        @if(!empty($u->is_locked))
                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item d-flex align-items-center gap-2 text-success"
                                    onclick="unlockUser({{ $u->id }})"
                                >
                                    <i class="fas fa-lock-open user-action-icon"></i>
                                    <span>Unlock Account</span>
                                </button>
                            </li>
                        @endif

                        <li>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2"
                                onclick="openStatusModal({{ $u->id }}, '{{ $statusStr }}', '{{ addslashes($u->name) }}')"
                            >
                                <i class="fas fa-shield-halved text-info user-action-icon"></i>
                                <span>Change Status</span>
                            </button>
                        </li>

                        <li>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2"
                                onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')"
                            >
                                <i class="fas fa-key text-warning user-action-icon"></i>
                                <span>Reset Password</span>
                            </button>
                        </li>

                        <li>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                onclick="confirmForceLogout({{ $u->id }}, '{{ addslashes($u->name) }}')"
                            >
                                <i class="fas fa-right-from-bracket user-action-icon"></i>
                                <span>Force Logout</span>
                            </button>
                        </li>

                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>

                        <li>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                onclick="confirmTrashUser({{ $u->id }}, '{{ addslashes($u->name) }}')"
                            >
                                <i class="fas fa-trash-can user-action-icon"></i>
                                <span>Move to Trash</span>
                            </button>
                        </li>
                    @endif

                </ul>
            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted small">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="user-empty-icon mb-3">
                    <i class="fas fa-users-slash"></i>
                </div>

                <span class="fw-semibold text-secondary">
                    No users or staff members found matching criteria.
                </span>
            </div>
        </td>
    </tr>
@endforelse

<style>
    /*
    |--------------------------------------------------------------------------
    | Users & Staff Table - Kaiadmin Classic White
    |--------------------------------------------------------------------------
    | Keep this stylesheet scoped to the users table.
    | If this partial is rendered through AJAX, consider moving these styles
    | to the parent Blade view to avoid repeated style injection.
    |--------------------------------------------------------------------------
    */

    .user-management-row {
        transition: background-color .18s ease;
    }

    .user-management-row:hover {
        background-color: #f8fafc;
    }

    .user-management-row > td {
        border-bottom: 1px solid #eef0f3;
        vertical-align: middle;
    }

    .user-serial-cell {
        font-size: 11.5px;
        white-space: nowrap;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        min-width: 40px;
        font-size: 14px;
        border-color: #e8ebf0 !important;
        background-color: #f8fafc !important;
    }

    .user-name {
        max-width: 175px;
        font-size: 13px;
        line-height: 1.4;
        font-weight: 600;
    }

    .user-designation {
        max-width: 180px;
        margin-top: 3px;
        font-size: 11px;
        line-height: 1.4;
    }

    .user-crown-badge {
        width: 19px;
        height: 19px;
        padding: 0;
        font-size: 9px;
    }

    .user-you-badge {
        padding: 3px 6px;
        font-size: 9px;
        font-weight: 600;
        border-radius: 5px;
    }

    .user-role-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 5px;
        white-space: nowrap;
    }

    .user-branch {
        max-width: 155px;
        margin-top: 5px;
        font-size: 11px;
    }

    .user-creator {
        max-width: 155px;
        margin-top: 3px;
        font-size: 10.5px;
    }

    .user-phone {
        font-size: 12px;
        line-height: 1.5;
        white-space: nowrap;
    }

    .user-email {
        max-width: 190px;
        margin-top: 4px;
        font-size: 10.5px;
        line-height: 1.5;
    }

    .user-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 9px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 5px;
        white-space: nowrap;
    }

    .user-created-date {
        font-size: 11px;
        line-height: 1.5;
        white-space: nowrap;
    }

    .user-last-login {
        margin-top: 5px;
        font-size: 10.5px;
        line-height: 1.5;
        white-space: nowrap;
    }

    .user-action-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 33px;
        height: 32px;
        padding: 0;
        color: #5b6472;
        background: #fff;
        border-color: #e5e7eb !important;
        border-radius: 6px;
        font-size: 12px;
    }

    .user-action-toggle::after {
        display: none;
    }

    .user-action-toggle:hover,
    .user-action-toggle:focus,
    .user-action-toggle.show {
        color: var(--bs-primary, #1572e8);
        background: #f0f5ff;
        border-color: #cbdafa !important;
    }

    .user-action-dropdown {
        min-width: 190px;
        z-index: 1080;
        border-radius: 8px;
        padding: 5px;
    }

    .user-action-dropdown .dropdown-item {
        min-height: 34px;
        padding: 7px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 500;
        color: #495057;
        transition: background-color .15s ease, color .15s ease;
    }

    .user-action-dropdown .dropdown-item:hover {
        background-color: #f3f6fb;
        color: #1572e8;
    }

    .user-action-dropdown .dropdown-item.text-danger:hover {
        background-color: #fff1f1;
        color: #dc3545;
    }

    .user-action-dropdown .dropdown-item.text-success:hover {
        background-color: #edfaf2;
        color: #198754;
    }

    .user-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 15px;
        min-width: 15px;
        font-size: 12px;
        text-align: center;
    }

    .user-action-dropdown .dropdown-divider {
        border-color: #edf0f3;
        opacity: 1;
    }

    .user-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        color: #9ba4b2;
        background: #f3f5f8;
        border: 1px solid #e9edf2;
        border-radius: 50%;
        font-size: 23px;
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    li.page-item {
        margin-right: 5px;
    }

    li.page-item.disabled {
        margin-left: 10px;
    }

    li.page-item.active {
        margin: 0 5px;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive Adjustments
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {
        .user-management-row > td {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            min-width: 36px;
        }

        .user-name {
            max-width: 140px;
            font-size: 12px;
        }

        .user-designation {
            max-width: 145px;
            font-size: 10px;
        }

        .user-email {
            max-width: 160px;
        }

        .user-role-badge,
        .user-status-badge {
            font-size: 9.5px;
        }

        .user-action-dropdown {
            min-width: 180px;
        }
    }

    @media (max-width: 575.98px) {
        .user-empty-icon {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
    }
</style>
