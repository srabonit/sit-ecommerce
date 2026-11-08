@php
    $uAuth = auth()->user();

    // Universal profile photo resolver
    $resolveHeaderPhoto = function($path) {
        if (empty($path)) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    $headerUserPhoto = $resolveHeaderPhoto($uAuth->photo ?? null);
@endphp


<style>
    /* Admin header - Kaiadmin White Classic */
    #admin-main-header {
        min-height: 64px;
        padding-top: 10px;
        padding-bottom: 10px;
        background: #fff;
        border: 1px solid #edf0f3;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
        z-index: 20;
    }

    #admin-main-header .header-right-actions {
        min-width: 0;
    }

    #admin-main-header .btn,
    #admin-main-header .header-action-btn,
    #admin-main-header .header-profile-btn {
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    #admin-main-header #mobileSidebarToggle {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #edf0f3 !important;
        border-radius: 9px;
    }

    #admin-main-header #mobileSidebarToggle:hover {
        color: #b45309;
        background: #fff7ed;
    }

    #admin-main-header .header-right-actions > a.btn {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-color: #e2e8f0 !important;
    }

    #admin-main-header .header-right-actions > a.btn:hover {
        color: #b45309 !important;
        background: #fff7ed;
        border-color: #fed7aa !important;
    }

    #admin-main-header .header-action-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        border-radius: 9px;
    }

    #admin-main-header .header-action-btn:hover {
        color: #b45309 !important;
        background: #fff7ed;
    }

    #admin-main-header .header-profile-btn {
        min-height: 40px;
        padding: 2px 5px !important;
        border-radius: 10px;
    }

    #admin-main-header .header-profile-btn:hover,
    #admin-main-header .header-profile-btn[aria-expanded="true"] {
        background: #f8fafc !important;
    }

    #admin-main-header .profile-avatar-circle {
        flex: 0 0 36px;
        border-color: #f1d6a8 !important;
        background: #fff3d6 !important;
    }

    #admin-main-header .profile-name {
        max-width: 140px;
        color: #1e293b !important;
        line-height: 1.25;
    }

    #admin-main-header .profile-email {
        max-width: 140px;
        line-height: 1.25;
    }

    #admin-main-header .dropdown-menu {
        min-width: 220px;
        margin-top: 10px;
        padding: 7px;
        border: 1px solid #edf0f3 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .10) !important;
    }

    #admin-main-header .dropdown-menu > li:first-child {
        background: #f8fafc !important;
        border-radius: 8px !important;
    }

    #admin-main-header .dropdown-item {
        min-height: 36px;
        color: #475569;
        border-radius: 7px !important;
    }

    #admin-main-header .dropdown-item:hover,
    #admin-main-header .dropdown-item:focus {
        color: #b45309;
        background: #fff7ed;
    }

    #admin-main-header .dropdown-item.text-danger:hover {
        color: #dc2626 !important;
        background: #fef2f2;
    }

    @media (max-width: 767.98px) {
        #admin-main-header {
            min-height: 58px;
            margin-bottom: 1rem !important;
            padding: 8px 10px !important;
            border-radius: 10px;
        }

        #admin-main-header .header-right-actions {
            gap: 8px !important;
        }
    }

    @media (max-width: 575.98px) {
        #admin-main-header .header-right-actions > a.btn {
            min-width: 34px;
            padding-right: 8px !important;
            padding-left: 8px !important;
        }

        #admin-main-header .header-action-btn {
            width: 32px;
            height: 32px;
        }

        #admin-main-header .profile-avatar-circle {
            width: 34px !important;
            height: 34px !important;
        }

        #admin-main-header .dropdown-menu {
            min-width: min(220px, calc(100vw - 24px));
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #admin-main-header *,
        #admin-main-header *::before,
        #admin-main-header *::after {
            transition: none !important;
        }
    }
</style>

<header class="admin-top-header d-flex align-items-center justify-content-between mb-4 position-relative px-2 px-md-3" id="admin-main-header">
    <div class="header-left-brand d-flex align-items-center gap-2" id="admin-header-brand-area">
        <button type="button" class="btn btn-light d-lg-none border-0 p-1" id="mobileSidebarToggle" onclick="toggleAdminSidebar()" title="Toggle Menu">
            <i class="fas fa-bars fs-5"></i>
        </button>
    </div>

    <div class="header-right-actions d-flex align-items-center gap-2 gap-md-3" id="admin-header-right-tools">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-light btn-sm text-dark border rounded-pill px-2 px-md-3 fw-semibold small shadow-xs" title="View Store">
            <i class="fas fa-external-link-alt me-1 text-primary"></i> <span class="d-none d-sm-inline">Store</span>
        </a>

        <button type="button" class="header-action-btn border-0 bg-transparent p-1" id="admin-header-notify-btn" title="Notifications">
            <i class="far fa-bell fs-5 text-secondary" id="admin-header-bell-icon"></i>
        </button>
        <a href="{{ route('admin.support.index') }}" class="header-action-btn text-decoration-none p-1" id="admin-header-mail-btn" title="Support Tickets">
            <i class="far fa-envelope fs-5 text-secondary" id="admin-header-envelope-icon"></i>
        </a>

        <!-- ক্লিকযোগ্য ইন্টারেক্টিভ প্রোফাইল ড্রপডাউন -->
        <div class="dropdown" id="admin-header-profile-dropdown">
            <button type="button" class="header-profile-btn border-0 bg-transparent p-0 d-flex align-items-center gap-2 text-decoration-none shadow-none" 
                    id="adminProfileMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <div class="profile-avatar-circle rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold overflow-hidden shadow-xs border" 
                     style="width: 36px; height: 36px;" id="admin-header-avatar">
                    @if(!empty($headerUserPhoto))
                        <img src="{{ $headerUserPhoto }}" alt="{{ $uAuth->name }}" class="w-100 h-100 object-fit-cover">
                    @else
                        {{ strtoupper(substr($uAuth->name ?? 'A', 0, 1)) }}
                    @endif
                </div>
                <div class="profile-text-info text-start d-none d-md-block" id="admin-header-profile-meta">
                    <div class="profile-name fw-bold text-dark small text-truncate" style="max-width: 140px;" id="admin-profile-name-text">
                        {{ $uAuth->name ?? 'Administrator' }}
                    </div>
                    <div class="profile-email text-muted text-truncate" style="font-size: 11px; max-width: 140px;" id="admin-profile-email-text">
                        {{ ucwords(str_replace('_', ' ', $uAuth->role ?? 'Staff')) }}
                    </div>
                </div>
                <i class="fas fa-chevron-down text-muted small ms-1 d-none d-md-inline" style="font-size: 10px;"></i>
            </button>

            <!-- প্রোফাইল ড্রপডাউন মেনু -->
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-2" style="min-width: 220px; z-index: 1080;">
                <li class="px-3 py-2 border-bottom mb-1 bg-light rounded-3">
                    <strong class="text-dark d-block text-truncate small">{{ $uAuth->name }}</strong>
                    <small class="text-muted d-block text-truncate font-monospace" style="font-size: 11px;">{{ $uAuth->email }}</small>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mt-1" style="font-size: 9.5px;">
                        {{ ucwords(str_replace('_', ' ', $uAuth->role ?? 'Staff')) }}
                    </span>
                </li>

                <li>
                    <a class="dropdown-item py-2 rounded-2 small d-flex align-items-center gap-2 fw-semibold" href="{{ route('admin.profile.index') }}">
                        <i class="fas fa-user-circle text-primary"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 rounded-2 small d-flex align-items-center gap-2" href="{{ route('admin.profile.index') }}#security-section">
                        <i class="fas fa-key text-warning"></i> Password
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST" class="m-0 p-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 rounded-2 small text-danger d-flex align-items-center gap-2 border-0 bg-transparent fw-semibold">
                            <i class="fas fa-right-from-bracket"></i> Sign out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>