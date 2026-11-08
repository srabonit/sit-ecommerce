@extends('layouts.admin')

@section('title', 'SMS Blacklist')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4 sms-blacklist-page">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">SMS Blacklist</h4>
            <p class="text-muted small mb-0">
                Restrict spam or abusive phone numbers from receiving transactional and campaign messages.
            </p>
        </div>

        <button type="button"
                class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-2"
                onclick="openAppModal('createBlacklistModal')">
            <i class="fas fa-shield-alt"></i>
            <span>Add Numbers</span>
        </button>
    </div>

    {{-- Search Filter --}}
    <div class="card sms-panel mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.blacklist.index') }}"
                  method="GET"
                  id="searchFilterForm"
                  class="row g-2 align-items-center">

                <div class="col-12 col-md-9">
                    <div class="input-group input-group-sm sms-search-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               name="search"
                               id="ajaxSearchInput"
                               class="form-control"
                               placeholder="Search by phone number or reason..."
                               value="{{ request('search') }}"
                               autocomplete="off">
                    </div>
                    <div class="form-text mt-2">
                        Search updates automatically as you type.
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="d-flex justify-content-md-end gap-2">
                        <button type="button"
                                id="btnClearSearch"
                                class="btn btn-light btn-sm px-3 text-muted {{ request()->filled('search') ? '' : 'd-none' }}">
                            <i class="fas fa-times me-1"></i> Clear
                        </button>

                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Blacklist Table --}}
    <div class="card sms-panel overflow-hidden">

        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Blocked Phone Numbers</h6>
                    <p class="text-muted small mb-0">Manage restricted mobile numbers and their blocking details.</p>
                </div>

                <span class="badge sms-section-badge">
                    <i class="fas fa-shield-alt me-1"></i> Blacklist Records
                </span>
            </div>
        </div>

        <div class="table-responsive sms-table-wrapper">
            <table class="table table-hover align-middle mb-0 sms-blacklist-table">
                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4" style="width: 25%;">Blocked Phone Number</th>
                        <th style="min-width: 260px;">Reason / Notes</th>
                        <th style="width: 20%;">Blocked Date</th>
                        <th class="pe-3 pe-md-4 text-end" style="width: 15%;">Action</th>
                    </tr>
                </thead>

                <tbody id="blacklistTableBody">
                    @include('admin.sms.blacklist.partials.table_rows', ['blacklists' => $blacklists])
                </tbody>
            </table>
        </div>

        {{-- AJAX Pagination --}}
        <div class="card-footer bg-white border-top py-3 px-3 px-md-4"
             id="paginationWrapper"
             style="{{ $blacklists->hasPages() ? '' : 'display: none;' }}">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <span class="small text-muted" id="entriesText">
                    Showing {{ $blacklists->firstItem() ?? 0 }} to {{ $blacklists->lastItem() ?? 0 }} of {{ $blacklists->total() }} entries
                </span>

                <div id="ajaxPaginationContainer" class="sms-pagination">
                    {{ $blacklists->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>

</div>

{{-- 1. Modal: Add Single or Multiple Phone Numbers --}}
<div class="modal fade" id="createBlacklistModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sms-modal-content">

            <div class="modal-header px-3 px-md-4 py-3">
                <div>
                    <h6 class="modal-title fw-bold text-dark mb-1">Block Phone Numbers</h6>
                    <p class="text-muted small mb-0">Add one or multiple numbers to your blacklist.</p>
                </div>

                <button type="button"
                        class="btn-close"
                        aria-label="Close"
                        onclick="closeAppModal('createBlacklistModal')"></button>
            </div>

            <form action="{{ route('admin.sms.blacklist.store') }}" method="POST">
                @csrf

                <div class="modal-body p-3 p-md-4">

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-dark mb-2">
                            Mobile Number(s) <span class="text-danger">*</span>
                        </label>

                        <textarea name="numbers"
                                  rows="5"
                                  class="form-control form-control-sm font-monospace sms-textarea"
                                  placeholder="Paste single or multiple phone numbers separated by comma, space or newline:&#10;01711000000&#10;01811000000&#10;+8801911000000"
                                  required></textarea>

                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Supports single or bulk numbers. Automatically normalizes BD prefixes (013-019, 096) and eliminates duplicates.
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold small text-dark mb-2">
                            Reason / Note (Optional)
                        </label>

                        <input type="text"
                               name="reason"
                               class="form-control form-control-sm"
                               placeholder="Fake order spam, abusive OTP calls">
                    </div>

                </div>

                <div class="modal-footer px-3 px-md-4 py-3">
                    <button type="button"
                            class="btn btn-light btn-sm px-3"
                            onclick="closeAppModal('createBlacklistModal')">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-ban me-1"></i> Block Numbers
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- 2. Modal: Unblock Confirmation --}}
<div class="modal fade" id="unblockConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content sms-modal-content text-center">

            <div class="modal-body p-4">

                <div class="sms-confirm-icon sms-confirm-warning mb-3">
                    <i class="fas fa-unlock-alt"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2">Unblock Number?</h6>

                <p class="text-muted small mb-4">
                    Are you sure you want to unblock
                    <strong class="text-dark font-monospace" id="unblockTargetPhone"></strong>?
                    This number will be able to receive SMS again.
                </p>

                <form id="unblockForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-light btn-sm flex-fill"
                                onclick="closeAppModal('unblockConfirmModal')">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-danger btn-sm flex-fill">
                            <i class="fas fa-unlock-alt me-1"></i> Unblock
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- 3. Modal: System Notification --}}
<div class="modal fade" id="systemAlertModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content sms-modal-content text-center">

            <div class="modal-body p-4">

                <div id="alertModalIcon" class="mb-3"></div>

                <h6 class="fw-bold text-dark mb-2" id="alertModalTitle">
                    Notification
                </h6>

                <p class="text-muted small mb-4" id="alertModalMessage"></p>

                <button type="button"
                        class="btn btn-dark btn-sm px-4 w-100"
                        onclick="closeAppModal('systemAlertModal')">
                    OK
                </button>

            </div>
        </div>
    </div>
</div>

{{-- Direct In-Content JavaScript Engine --}}
<script>
function openAppModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (window.jQuery && typeof window.jQuery(el).modal === 'function') {
        window.jQuery(el).modal('show');
    } else {
        el.classList.add('show');
        el.style.display = 'block';
        el.removeAttribute('aria-hidden');
        if (!document.querySelector('.modal-backdrop')) {
            const b = document.createElement('div');
            b.className = 'modal-backdrop fade show';
            document.body.appendChild(b);
        }
        document.body.classList.add('modal-open');
    }
}

function closeAppModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    if (window.bootstrap && window.bootstrap.Modal) {
        const inst = bootstrap.Modal.getInstance(el);
        if (inst) inst.hide();
        else bootstrap.Modal.getOrCreateInstance(el).hide();
    } else if (window.jQuery && typeof window.jQuery(el).modal === 'function') {
        window.jQuery(el).modal('hide');
    } else {
        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
        const b = document.querySelector('.modal-backdrop');
        if (b) b.remove();
        document.body.classList.remove('modal-open');
    }
}

function showModalAlert(title, message, isSuccess = true) {
    document.getElementById('alertModalTitle').innerText = title;
    document.getElementById('alertModalMessage').innerText = message;
    const iconContainer = document.getElementById('alertModalIcon');

    if (isSuccess) {
        iconContainer.innerHTML = '<i class="fas fa-check-circle text-success fs-1"></i>';
    } else {
        iconContainer.innerHTML = '<i class="fas fa-times-circle text-danger fs-1"></i>';
    }
    openAppModal('systemAlertModal');
}

(function() {
    function initBlacklistModule() {
        const tableBody          = document.getElementById('blacklistTableBody');
        const paginationWrapper  = document.getElementById('paginationWrapper');
        const ajaxPagination     = document.getElementById('ajaxPaginationContainer');
        const entriesText        = document.getElementById('entriesText');
        const searchInput        = document.getElementById('ajaxSearchInput');
        const searchForm         = document.getElementById('searchFilterForm');
        const btnClearSearch     = document.getElementById('btnClearSearch');

        // Flash Session Alerts displayed in Modal
        @if(session('success'))
            showModalAlert('Success', "{{ session('success') }}", true);
        @endif
        @if(session('error'))
            showModalAlert('Notice', "{{ session('error') }}", false);
        @endif

        // 1. AJAX Fetch Engine for Search & Pagination
        function fetchBlacklistAjax(url) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Searching blacklist records...
                    </td>
                </tr>
            `;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    tableBody.innerHTML = data.html;
                    ajaxPagination.innerHTML = data.pagination || '';
                    entriesText.innerText = `Showing ${data.from} to ${data.to} of ${data.total} entries`;
                    paginationWrapper.style.display = (data.total > 20 || data.pagination) ? '' : 'none';
                    window.history.pushState(null, '', url);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Failed to load records.</td></tr>`;
                }
            })
            .catch(() => {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Connection error while searching.</td></tr>`;
            });
        }

        function triggerSearch() {
            const val = searchInput.value.trim();
            btnClearSearch.classList.toggle('d-none', !val);

            const params = new URLSearchParams();
            if (val) params.set('search', val);

            const fetchUrl = `{{ route('admin.sms.blacklist.index') }}?${params.toString()}`;
            fetchBlacklistAjax(fetchUrl);
        }

        // Live Debounced Instant Search
        let searchDebounceTimer = null;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(triggerSearch, 300);
            });
        }

        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                triggerSearch();
            });
        }

        if (btnClearSearch) {
            btnClearSearch.addEventListener('click', function() {
                searchInput.value = '';
                triggerSearch();
            });
        }

        // Global Event Delegation for Unblock & Pagination Links
        document.addEventListener('click', function(e) {
            // Unblock Button Modal Trigger
            const unblockBtn = e.target.closest('.btn-unblock');
            if (unblockBtn) {
                e.preventDefault();
                const action = unblockBtn.getAttribute('data-action');
                const phone  = unblockBtn.getAttribute('data-phone');

                document.getElementById('unblockTargetPhone').innerText = phone;
                document.getElementById('unblockForm').setAttribute('action', action);
                openAppModal('unblockConfirmModal');
                return;
            }

            // AJAX Pagination Click
            const pageLink = e.target.closest('#ajaxPaginationContainer a.page-link');
            if (pageLink) {
                e.preventDefault();
                const targetUrl = pageLink.getAttribute('href');
                if (targetUrl) {
                    fetchBlacklistAjax(targetUrl);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBlacklistModule);
    } else {
        initBlacklistModule();
    }
})();
</script>

<style>
/* =========================================
   SMS BLACKLIST - KAIADMIN CLASSIC THEME
========================================= */

.sms-blacklist-page {
    --sms-primary: #1572e8;
    --sms-primary-dark: #1264cc;
    --sms-ink: #1f2937;
    --sms-muted: #6b7280;
    --sms-border: #e5e7eb;
    --sms-surface: #ffffff;
    --sms-soft: #f7f9fc;
    --sms-radius: 10px;
    color: var(--sms-ink);
    min-width: 0;
}

.sms-blacklist-page .card.sms-panel {
    background: var(--sms-surface);
    border: 1px solid var(--sms-border) !important;
    border-radius: var(--sms-radius);
    box-shadow: 0 2px 8px rgba(31, 41, 55, 0.035);
}

.sms-blacklist-page .btn {
    border-radius: 6px;
    font-weight: 500;
    transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.sms-blacklist-page .btn-primary {
    background-color: var(--sms-primary);
    border-color: var(--sms-primary);
}

.sms-blacklist-page .btn-primary:hover {
    background-color: var(--sms-primary-dark);
    border-color: var(--sms-primary-dark);
}

.sms-blacklist-page .btn:focus-visible,
.sms-blacklist-page .form-control:focus-visible {
    outline: 2px solid rgba(21, 114, 232, .25);
    outline-offset: 2px;
}

.sms-blacklist-page .form-control,
.sms-blacklist-page .input-group-text {
    border-color: #dfe3e9;
    font-size: 13px;
}

.sms-blacklist-page .form-control {
    min-height: 36px;
    color: var(--sms-ink);
    border-radius: 6px;
}

.sms-blacklist-page .form-control::placeholder {
    color: #9ca3af;
}

.sms-blacklist-page .form-control:focus {
    border-color: var(--sms-primary);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
}

.sms-blacklist-page .sms-search-group .input-group-text {
    background: var(--sms-soft);
    color: #8a94a6;
    border-radius: 6px 0 0 6px;
    padding-left: 12px;
    padding-right: 12px;
}

.sms-blacklist-page .sms-search-group .form-control {
    border-radius: 0 6px 6px 0;
}

.sms-blacklist-page .form-text {
    color: var(--sms-muted);
    font-size: 11px;
    line-height: 1.5;
}

.sms-blacklist-page .sms-section-badge {
    color: #526174;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 7px 10px;
    font-size: 11px;
    font-weight: 600;
}

.sms-blacklist-page .sms-table-wrapper {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.sms-blacklist-page .sms-blacklist-table {
    min-width: 720px;
    border-collapse: separate;
    border-spacing: 0;
}

.sms-blacklist-page .sms-blacklist-table thead th {
    background: #f8fafc;
    color: #64748b;
    border-bottom: 1px solid var(--sms-border);
    padding: 13px 12px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .45px;
    text-transform: uppercase;
    white-space: nowrap;
}

.sms-blacklist-page .sms-blacklist-table tbody td {
    padding: 13px 12px;
    border-bottom: 1px solid #edf0f4;
    color: #475569;
    font-size: 12px;
    vertical-align: middle;
}

.sms-blacklist-page .sms-blacklist-table tbody tr:last-child td {
    border-bottom: 0;
}

.sms-blacklist-page .sms-blacklist-table tbody tr:hover {
    background: #fafcff;
}

.sms-blacklist-page .sms-blacklist-table tbody td:first-child {
    padding-left: 16px;
}

.sms-blacklist-page .sms-blacklist-table tbody td:last-child {
    padding-right: 16px;
}

.sms-blacklist-page .sms-blacklist-table .font-monospace {
    font-size: 12px;
    font-weight: 600;
    color: #334155;
}

.sms-blacklist-page .card-footer {
    border-color: var(--sms-border) !important;
}

.sms-blacklist-page .sms-pagination .pagination {
    margin: 0;
    gap: 4px;
    flex-wrap: wrap;
}

.sms-blacklist-page .sms-pagination .page-link {
    min-width: 30px;
    text-align: center;
    border-radius: 6px !important;
    font-size: 12px;
    padding: 5px 9px;
    color: #526174;
    border-color: #e2e8f0;
}

.sms-blacklist-page .sms-pagination .page-item.active .page-link {
    background-color: var(--sms-primary);
    border-color: var(--sms-primary);
    color: #fff;
}

.sms-blacklist-page .sms-pagination .page-item.disabled .page-link {
    color: #a0a8b5;
    background: #f8fafc;
}

.sms-blacklist-page .modal-content.sms-modal-content {
    border: 1px solid rgba(226, 232, 240, .9);
    border-radius: 12px;
    box-shadow: 0 16px 50px rgba(15, 23, 42, .15);
    overflow: hidden;
}

.sms-blacklist-page .modal-header {
    border-bottom: 1px solid var(--sms-border);
    background: #fff;
}

.sms-blacklist-page .modal-footer {
    border-top: 1px solid var(--sms-border);
    background: #f8fafc;
}

.sms-blacklist-page .modal-body {
    background: #fff;
}

.sms-blacklist-page .modal .form-label {
    font-size: 12px;
}

.sms-blacklist-page .sms-textarea {
    min-height: 120px;
    line-height: 1.7;
    resize: vertical;
    background: #fbfcfe;
}

.sms-blacklist-page .sms-confirm-icon {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
}

.sms-blacklist-page .sms-confirm-warning {
    color: #d97706;
    background: #fff7ed;
    border: 1px solid #ffedd5;
}

/* Tablet */
@media (max-width: 767.98px) {
    .sms-blacklist-page {
        padding-top: 18px !important;
        padding-bottom: 20px !important;
    }

    .sms-blacklist-page .sms-panel .card-header {
        padding: 13px 14px !important;
    }

    .sms-blacklist-page .sms-blacklist-table {
        min-width: 700px;
    }

    .sms-blacklist-page .sms-pagination {
        width: 100%;
    }

    .sms-blacklist-page .sms-pagination .pagination {
        justify-content: flex-start;
    }
}

/* Small Mobile */
@media (max-width: 380px) {
    .sms-blacklist-page .sms-section-badge {
        font-size: 10px;
        padding: 6px 8px;
    }

    .sms-blacklist-page .sms-blacklist-table {
        min-width: 680px;
    }

    .sms-blacklist-page .modal-dialog {
        margin: 12px;
    }
}
</style>
@endsection

@section('content')
    @yield('admin_content')
@endsection