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

<header class="admin-top-header d-flex align-items-center justify-content-between mb-4 position-relative px-2 px-md-3" id="admin-main-header">
    <div class="header-left-brand d-flex align-items-center gap-2" id="admin-header-brand-area">
        <button type="button" class="btn btn-light d-lg-none border-0 p-1" id="mobileSidebarToggle" onclick="toggleAdminSidebar()" title="Toggle Menu">
            <i class="fas fa-bars fs-5"></i>
        </button>
    </div>

    <div class="header-right-actions d-flex align-items-center gap-2 gap-md-3" id="admin-header-right-tools">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-light btn-sm text-dark border rounded-pill px-2 px-md-3 fw-semibold small shadow-xs" title="View Store">
            <i class="fas fa-external-link-alt me-1 text-primary"></i> <span class="d-none d-sm-inline">Live Store</span>
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
                        <i class="fas fa-user-circle text-primary"></i> My Profile & Activity
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 rounded-2 small d-flex align-items-center gap-2" href="{{ route('admin.profile.index') }}#security-section">
                        <i class="fas fa-key text-warning"></i> Change Password
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST" class="m-0 p-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 rounded-2 small text-danger d-flex align-items-center gap-2 border-0 bg-transparent fw-semibold">
                            <i class="fas fa-right-from-bracket"></i> Sign Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>