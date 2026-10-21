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
        <style>
            li.page-item.disabled {
                margin-left: 10px;}
                li.page-item.active {
        margin: 0px 5px;
    }

    li.page-item {
        margin-right: 5px;
    }
        </style>
        <tr id="user-row-{{ $u->id }}" class="align-middle">
            <!-- Bulk Selection Checkbox -->
            <td class="text-center py-2 px-2">
                @if(!$isSelf && (!$isSuper || auth()->user()->isSuperAdmin()))
                    <input type="checkbox" class="form-check-input user-select-checkbox shadow-none mt-0" value="{{ $u->id }}" onchange="handleUserSelectionChange()" style="border-radius: 4px; cursor: pointer;">
                @else
                    <span class="text-muted opacity-40" title="Protected System Account"><i class="fas fa-lock fa-xs"></i></span>
                @endif
            </td>

            <!-- Serial Index -->
            <td class="font-monospace text-muted fw-bold py-2 px-2 bn-digit" style="font-size: 11.5px;">
                {{ $users->firstItem() + $index }}
            </td>

            <!-- Staff / User Identity -->
            <td class="py-2 px-2" style="min-width: 200px;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-light border text-secondary d-flex align-items-center justify-content-center fw-bold overflow-hidden flex-shrink-0 shadow-xs" style="width: 36px; height: 36px; font-size: 12.5px;">
                        @if(!empty($avatarPhoto))
                            <img src="{{ $avatarPhoto }}" alt="{{ $u->name }}" class="w-100 h-100 object-fit-cover" onerror="this.style.display='none'; this.parentElement.innerText='{{ strtoupper(substr($u->name, 0, 1)) }}';">
                        @else
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0 pe-1">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <strong class="text-dark text-truncate" style="max-width: 145px; font-size: 13px;">{{ $u->name }}</strong>
                            @if($isSuper)
                                <span class="badge bg-danger p-1 rounded-circle ms-1" title="Super Admin"><i class="fas fa-crown text-white" style="font-size: 8px;"></i></span>
                            @endif
                            @if($isSelf)
                                <span class="badge ms-1 bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5 fw-semibold" style="font-size: 9px; border-radius: 5px !important;">You</span>
                            @endif
                        </div>
                        <small class="text-muted d-block text-truncate mt-0.5" style="font-size: 11px; max-width: 165px; line-height: 1.25;">
                            {{ $u->designation ?: ($isCustomer ? 'Registered Customer' : 'Staff Member') }}
                        </small>
                    </div>
                </div>
            </td>

            <!-- Role, Branch & Creator Metadata -->
            <td class="py-2 px-2" style="min-width: 160px;">
                @if($isCustomer)
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 mb-1 d-inline-flex align-items-center font-monospace" style="font-size: 9.5px; border-radius: 5px !important;">
                    Customer
                    </span>
                @elseif($isSuper)
                    <span class="badge bg-danger text-white px-2 py-0.5 mb-1 d-inline-flex align-items-center font-monospace shadow-xs" style="font-size: 9.5px; border-radius: 5px !important;">
                    Super Admin
                    </span>
                @elseif(!empty($rawRole))
                    <span class="badge badge-soft-primary px-2 py-0.5 mb-1 d-inline-flex align-items-center font-monospace shadow-xs" style="font-size: 9.5px; border-radius: 5px !important;">
                    {{ ucwords($cleanRole) }}
                    </span>
                @else
                    <span class="badge badge-soft-secondary px-2 py-0.5 mb-1 d-inline-flex align-items-center font-monospace" style="font-size: 9.5px; border-radius: 5px !important;">
                    Staff
                    </span>
                @endif

                @if(!empty($u->branch_name))
                    <small class="d-block text-secondary text-truncate mt-0.5" style="font-size: 11px; max-width: 140px;">
                    {{ $u->branch_name }}
                    </small>
                @endif

                @if(!empty($u->creator_name))
                    <small class="text-muted d-block text-truncate mt-0.5" style="font-size: 10.5px; max-width: 140px;">
                   By: <strong>{{ $u->creator_name }}</strong>
                    </small>
                @else
                    <small class="text-muted d-block mt-0.5" style="font-size: 10.5px;">
                     <em>Self-Registered</em>
                    </small>
                @endif
            </td>

            <!-- Contact Channels -->
            <td class="py-2 px-2 text-nowrap" style="min-width: 140px;">
                <div class="font-monospace small text-dark fw-semibold bn-digit" style="font-size: 12px;">{{ $u->phone ?: 'No Phone' }}</div>
                <small class="text-muted text-break d-block font-monospace mt-0.5" style="font-size: 10.5px;">{{ $u->email }}</small>
            </td>

            <!-- Access & Security Status -->
            <td class="text-center py-2 px-2 text-nowrap">
                @if(!empty($u->is_locked))
                    <span class="badge badge-soft-danger px-2 py-1 d-inline-flex align-items-center fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;" title="{{ $u->locked_reason ?: 'Account Locked' }}">
                        <i class="fas fa-lock me-1"></i> Locked
                    </span>
                @elseif($statusStr === 'active')
                    <span class="badge badge-soft-success px-2 py-1 d-inline-flex align-items-center fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;">
                        <i class="fas fa-circle-check me-1"></i> Active
                    </span>
                @elseif($statusStr === 'suspended')
                    <span class="badge badge-soft-warning px-2 py-1 d-inline-flex align-items-center fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;">
                        <i class="fas fa-triangle-exclamation me-1"></i> Suspended
                    </span>
                @elseif($statusStr === 'frozen')
                    <span class="badge badge-soft-info px-2 py-1 d-inline-flex align-items-center fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;">
                        <i class="fas fa-snowflake me-1"></i> Frozen
                    </span>
                @elseif($statusStr === 'blocked')
                    <span class="badge badge-soft-danger px-2 py-1 d-inline-flex align-items-center fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;">
                        <i class="fas fa-ban me-1"></i> Blocked
                    </span>
                @else
                    <span class="badge badge-soft-secondary px-2 py-1 d-inline-flex align-items-center fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;">
                        <i class="fas fa-power-off me-1"></i> Inactive
                    </span>
                @endif
            </td>

            <!-- Activity Timestamps -->
            <td class="py-2 px-2 text-nowrap">
                <small class="text-dark d-block font-monospace bn-digit" style="font-size: 11px;">
                    <i class="far fa-calendar-plus text-muted me-1.5"></i> {{ date('d M Y', strtotime($u->created_at)) }}
                </small>
                @if(!empty($u->last_login_at))
                    <small class="text-secondary d-block font-monospace bn-digit mt-0.5" style="font-size: 10.5px;">
                        <i class="fas fa-right-to-bracket text-muted me-1.5"></i> {{ date('d M, h:i A', strtotime($u->last_login_at)) }}
                    </small>
                @else
                    <small class="text-muted d-block font-monospace mt-0.5" style="font-size: 10px;">
                        <i class="far fa-clock text-muted me-1.5"></i> Never Logged In
                    </small>
                @endif
            </td>

            <!-- Action Dropdown Menu -->
            <td class="text-end text-nowrap py-2 px-2">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-light btn-sm border dropdown-toggle px-2 py-1 shadow-xs" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            data-bs-boundary="viewport"
                            data-bs-display="dynamic"
                            aria-expanded="false"
                            style="border-radius: 5px !important; font-size: 11px;">
                        <i class="fas fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border p-1" style="min-width: 185px; z-index: 1080; border-radius: 6px !important;">
                        <li>
                            <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('admin.roles.users.show', $u->id) }}">
                                <i class="fas fa-eye text-primary me-1.5" style="width: 14px; text-align: center;"></i> <span>View Profile</span>
                            </a>
                        </li>

                        @if(!$isCustomer)
                            <li>
                                <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('admin.roles.users.edit_staff', $u->id) }}">
                                    <i class="fas fa-user-pen text-secondary me-1.5" style="width: 14px; text-align: center;"></i> <span>Edit Staff</span>
                                </a>
                            </li>
                        @endif

                        @if($isSelf)
                            <li>
                                <button type="button" class="dropdown-item py-1.5 d-flex align-items-center gap-2" onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                    <i class="fas fa-key text-warning me-1.5" style="width: 14px; text-align: center;"></i> <span>Reset My Password</span>
                                </button>
                            </li>
                        @endif

                        @if($canManage)
                            <li><hr class="dropdown-divider my-1"></li>

                            @if(!empty($u->is_locked))
                                <li>
                                    <button type="button" class="dropdown-item py-1.5 text-success d-flex align-items-center gap-2" onclick="unlockUser({{ $u->id }})">
                                        <i class="fas fa-lock-open me-1.5" style="width: 14px; text-align: center;"></i> <span>Unlock Account</span>
                                    </button>
                                </li>
                            @endif

                            <li>
                                <button type="button" class="dropdown-item py-1.5 d-flex align-items-center gap-2" onclick="openStatusModal({{ $u->id }}, '{{ $statusStr }}', '{{ addslashes($u->name) }}')">
                                    <i class="fas fa-shield-halved text-info me-1.5" style="width: 14px; text-align: center;"></i> <span>Change Status</span>
                                </button>
                            </li>

                            <li>
                                <button type="button" class="dropdown-item py-1.5 d-flex align-items-center gap-2" onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                    <i class="fas fa-key text-warning me-1.5" style="width: 14px; text-align: center;"></i> <span>Reset Password</span>
                                </button>
                            </li>

                            <li>
                                <button type="button" class="dropdown-item py-1.5 text-danger d-flex align-items-center gap-2" onclick="confirmForceLogout({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                    <i class="fas fa-right-from-bracket me-1.5" style="width: 14px; text-align: center;"></i> <span>Force Logout</span>
                                </button>
                            </li>

                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <button type="button" class="dropdown-item py-1.5 text-danger d-flex align-items-center gap-2" onclick="confirmTrashUser({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                    <i class="fas fa-trash-can me-1.5" style="width: 14px; text-align: center;"></i> <span>Move to Trash</span>
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
            <i class="fas fa-users-slash fa-2x mb-2 d-block opacity-40"></i>
            <span class="fw-semibold">No users or staff members found matching criteria.</span>
        </td>
    </tr>
@endforelse