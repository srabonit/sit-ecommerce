@extends('layouts.admin')

@section('title', 'SMS Templates')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4 sms-templates-page">

    {{-- Page Header --}}
    <div class="sms-page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="sms-page-title mb-1">SMS Templates</h4>
            <p class="sms-page-subtitle mb-0">
              Manage order, promotional & notification templates.
            </p>
        </div>

        <a href="{{ route('admin.sms.templates.create') }}"
           class="btn btn-primary sms-btn-primary d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-plus me-2"></i>
            New Template
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="card sms-card mb-3">
        <div class="card-body sms-filter-body">
            <form action="{{ route('admin.sms.templates.index') }}"
                  method="GET"
                  id="filterForm"
                  class="row g-3 align-items-center">

                <div class="col-12 col-md-6">
                    <div class="input-group sms-search-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               name="search"
                               id="searchInput"
                               class="form-control"
                               placeholder="Search templates by name, key, or message..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <select name="type"
                            id="typeSelect"
                            class="form-select sms-form-control">
                        <option value="">All Categories</option>
                        <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>
                            Order Notifications
                        </option>
                        <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>
                            Auth &amp; OTP
                        </option>
                        <option value="marketing" {{ request('type') === 'marketing' ? 'selected' : '' }}>
                            Marketing
                        </option>
                        <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>
                            General Alerts
                        </option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="d-flex justify-content-sm-end gap-2">
                        <a href="{{ route('admin.sms.templates.index') }}"
                           class="btn btn-light sms-btn-light {{ request()->hasAny(['search', 'type']) ? '' : 'd-none' }}"
                           id="btnClearFilter">
                            <i class="fas fa-times me-1"></i>
                            Clear
                        </a>

                        <button type="submit" class="btn btn-secondary sms-btn-filter">
                            <i class="fas fa-filter me-1"></i>
                            Filter
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Templates Table --}}
    <div class="card sms-card sms-table-card">

        <div class="sms-table-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="sms-section-title mb-1">Template List</h6>
                <p class="sms-section-subtitle mb-0">
                    View and manage your SMS notification templates.
                </p>
            </div>
        </div>

        <div class="table-responsive sms-table-responsive">
            <table class="table table-hover align-middle mb-0 sms-template-table">
                <thead>
                    <tr>
                        <th class="sms-template-column">Template</th>
                        <th class="sms-message-column">Message</th>
                        <th class="sms-format-column">Format &amp; Parts</th>
                        <th class="sms-status-column">Status</th>
                        <th class="sms-actions-column text-end">Actions</th>
                    </tr>
                </thead>

                <tbody id="templateTableBody">
                    @include('admin.sms.templates.partials.table_rows', ['templates' => $templates])
                </tbody>
            </table>
        </div>

        {{-- AJAX Pagination Container --}}
        <div class="card-footer sms-pagination-footer"
             id="paginationWrapper"
             style="{{ $templates->hasPages() ? '' : 'display: none;' }}">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <span class="sms-entries-text" id="entriesText">
                    Showing {{ $templates->firstItem() ?? 0 }} to {{ $templates->lastItem() ?? 0 }} of {{ $templates->total() }} templates
                </span>

                <div id="ajaxPaginationContainer">
                    {{ $templates->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>
</div>

{{-- Message Preview Modal --}}
<div class="modal fade sms-app-modal"
     id="templatePreviewModal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sms-modal-content">

            <div class="modal-header sms-modal-header">
                <div>
                    <h6 class="modal-title sms-modal-title mb-1" id="previewModalTitle">
                        Message Preview
                    </h6>
                    <code class="sms-modal-key" id="previewModalKey"></code>
                </div>

                <button type="button"
                        class="btn-close"
                        data-dismiss="modal"
                        data-bs-dismiss="modal"
                        onclick="closeAppModal('templatePreviewModal')">
                </button>
            </div>

            <div class="modal-body sms-modal-body">

                <label class="form-label sms-modal-label">
                    Message Body
                </label>

                <div class="sms-message-preview"
                     id="previewModalBody"
                     style="white-space: pre-wrap; word-break: break-word;">
                </div>

                <label class="form-label sms-modal-label">
                    Configured Parameters
                </label>

                <div id="previewModalVars" class="d-flex flex-wrap gap-2"></div>

            </div>

            <div class="modal-footer sms-modal-footer">
                <button type="button"
                        class="btn btn-secondary sms-modal-close-btn"
                        data-dismiss="modal"
                        data-bs-dismiss="modal"
                        onclick="closeAppModal('templatePreviewModal')">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

{{-- System Alert Modal --}}
<div class="modal fade sms-app-modal"
     id="systemAlertModal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content sms-modal-content sms-alert-modal-content">

            <div class="modal-body sms-alert-modal-body">

                <div id="alertModalIcon" class="sms-alert-icon mb-3"></div>

                <h6 class="sms-modal-title mb-2" id="alertModalTitle">
                    Notification
                </h6>

                <p class="sms-alert-message mb-3" id="alertModalMessage"></p>

                <button type="button"
                        class="btn btn-dark sms-alert-confirm-btn"
                        data-dismiss="modal"
                        data-bs-dismiss="modal"
                        onclick="closeAppModal('systemAlertModal')">
                    OK
                </button>

            </div>

        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade sms-app-modal"
     id="deleteConfirmModal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content sms-modal-content sms-alert-modal-content">

            <div class="modal-body sms-alert-modal-body">

                <div class="sms-delete-icon mb-3">
                    <i class="fas fa-exclamation-circle"></i>
                </div>

                <h6 class="sms-modal-title mb-2">
                    Delete Template?
                </h6>

                <p class="sms-alert-message mb-3" id="deleteConfirmText">
                    Are you sure you want to delete this custom template?
                </p>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-light sms-modal-cancel-btn flex-fill"
                                data-dismiss="modal"
                                data-bs-dismiss="modal"
                                onclick="closeAppModal('deleteConfirmModal')">
                            Cancel
                        </button>

                        <button type="submit"
                                class="btn btn-danger sms-modal-delete-btn flex-fill">
                            Delete
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>

{{-- Direct In-Content JavaScript Engine --}}
<script>
// Universal Fail-Safe Modal Handler (Works with Bootstrap 5, Bootstrap 4, jQuery, or Vanilla JS)
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
    function initTemplateModule() {
        const tableBody          = document.getElementById('templateTableBody');
        const paginationWrapper  = document.getElementById('paginationWrapper');
        const ajaxPagination     = document.getElementById('ajaxPaginationContainer');
        const entriesText        = document.getElementById('entriesText');
        const filterForm         = document.getElementById('filterForm');
        const searchInput        = document.getElementById('searchInput');
        const typeSelect         = document.getElementById('typeSelect');
        const btnClearFilter     = document.getElementById('btnClearFilter');

        // Check Server-Side Flash Alerts on load
        @if(session('modal_success') || session('success'))
            showModalAlert('Success', "{{ session('modal_success') ?? session('success') }}", true);
        @endif
        @if(session('modal_error') || session('error'))
            showModalAlert('Notice', "{{ session('modal_error') ?? session('error') }}", false);
        @endif

        // Global Event Delegation for Dynamic Table Content (Never breaks across AJAX)
        document.addEventListener('click', function(e) {
            // 1. View Message Modal Trigger
            const previewBtn = e.target.closest('.btn-preview');
            if (previewBtn) {
                e.preventDefault();
                const id = previewBtn.getAttribute('data-id');
                const dataBox = document.getElementById('tpl-data-' + id);
                if (dataBox) {
                    const name = dataBox.getAttribute('data-name');
                    const key  = dataBox.getAttribute('data-key');
                    const body = dataBox.querySelector('.tpl-body-content').innerText;
                    const vars = JSON.parse(dataBox.querySelector('.tpl-vars-content').innerText || '[]');

                    document.getElementById('previewModalTitle').innerText = name;
                    document.getElementById('previewModalKey').innerText = key ? `Key: ${key}` : '';
                    document.getElementById('previewModalBody').innerText = body;

                    const varsContainer = document.getElementById('previewModalVars');
                    varsContainer.innerHTML = '';

                    if (Array.isArray(vars) && vars.length > 0) {
                        vars.forEach(v => {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-light text-dark border px-2 py-1 font-monospace';
                            badge.style.fontSize = '11px';
                            badge.style.borderRadius = '4px';
                            badge.textContent = `{${v}}`;
                            varsContainer.appendChild(badge);
                        });
                    } else {
                        varsContainer.innerHTML = '<span class="text-muted small" style="font-size: 11px;">No dynamic parameters defined.</span>';
                    }

                    openAppModal('templatePreviewModal');
                }
                return;
            }

            // 2. Delete Confirmation Modal Trigger
            const deleteBtn = e.target.closest('.btn-delete-confirm');
            if (deleteBtn) {
                e.preventDefault();
                const action = deleteBtn.getAttribute('data-action');
                const name   = deleteBtn.getAttribute('data-name');
                document.getElementById('deleteConfirmText').innerText = `Are you sure you want to permanently delete "${name}"?`;
                document.getElementById('deleteForm').setAttribute('action', action);
                openAppModal('deleteConfirmModal');
                return;
            }

            // 3. AJAX Pagination Links
            const pageLink = e.target.closest('#ajaxPaginationContainer a.page-link');
            if (pageLink) {
                e.preventDefault();
                const targetUrl = pageLink.getAttribute('href');
                if (targetUrl) {
                    fetchTemplatesAjax(targetUrl);
                }
                return;
            }
        });

        // 4. Status Toggle Switch (AJAX)
        document.addEventListener('change', function(e) {
            const toggle = e.target.closest('.status-toggle');
            if (toggle) {
                const url = toggle.getAttribute('data-url');
                const isChecked = toggle.checked;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showModalAlert('Status Updated', data.message, true);
                    } else {
                        toggle.checked = !isChecked;
                        showModalAlert('Error', data.message || 'Failed to update status.', false);
                    }
                })
                .catch(() => {
                    toggle.checked = !isChecked;
                    showModalAlert('Connection Error', 'Could not communicate with the server.', false);
                });
            }
        });

        // 5. Core AJAX Filter & Pagination Fetch Engine
        function fetchTemplatesAjax(url) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Loading templates...
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
                    entriesText.innerText = `Showing ${data.from} to ${data.to} of ${data.total} templates`;
                    paginationWrapper.style.display = (data.total > 20 || data.pagination) ? '' : 'none';
                    window.history.pushState(null, '', url);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load templates.</td></tr>`;
                }
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Network error loading templates.</td></tr>`;
            });
        }

        function triggerFilter() {
            const params = new URLSearchParams();
            if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
            if (typeSelect.value) params.set('type', typeSelect.value);

            if (btnClearFilter) {
                btnClearFilter.classList.toggle('d-none', !params.toString());
            }

            const fetchUrl = `{{ route('admin.sms.templates.index') }}?${params.toString()}`;
            fetchTemplatesAjax(fetchUrl);
        }

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                triggerFilter();
            });
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', triggerFilter);
        }

        let debounceTimer = null;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(triggerFilter, 350);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemplateModule);
    } else {
        initTemplateModule();
    }
})();
</script>

<style>
/* =========================================================
   SMS TEMPLATES - KAIADMIN LITE 1.2.0 CLASSIC / WHITE
   Scoped styles: affect only this page
========================================================= */

.sms-templates-page {
    --sms-primary: var(--bs-primary, #1572e8);
    --sms-primary-soft: #edf4ff;
    --sms-text: #2a2f5b;
    --sms-muted: #8d9498;
    --sms-border: #ebedf2;
    --sms-surface: #ffffff;
    --sms-page-bg: #f8f9fa;
    --sms-radius: 8px;
    color: var(--sms-text);
    min-width: 0;
}

/* Page Header */
.sms-templates-page .sms-page-header {
    min-width: 0;
}

.sms-templates-page .sms-page-title {
    color: var(--sms-text);
    font-size: clamp(19px, 2vw, 22px);
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: -0.35px;
}

.sms-templates-page .sms-page-subtitle {
    color: var(--sms-muted);
    font-size: 13px;
    line-height: 1.6;
}

.sms-templates-page .sms-btn-primary {
    min-height: 38px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 2px 5px rgba(21, 114, 232, 0.12);
}

.sms-templates-page .sms-btn-primary i {
    font-size: 12px;
}

.sms-templates-page .sms-btn-primary:hover {
    box-shadow: 0 4px 10px rgba(21, 114, 232, 0.18);
}

/* Common Cards */
.sms-templates-page .sms-card {
    background: var(--sms-surface);
    border: 1px solid var(--sms-border);
    border-radius: var(--sms-radius);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.035);
    min-width: 0;
}

.sms-templates-page .sms-filter-body {
    padding: 18px;
}

/* Form Controls */
.sms-templates-page .sms-search-group {
    min-height: 39px;
}

.sms-templates-page .sms-search-group .input-group-text {
    background: #fff;
    border: 1px solid #e6e8eb;
    border-right: 0;
    border-radius: 6px 0 0 6px;
    color: #9ba1a6;
    padding: 0 12px;
}

.sms-templates-page .sms-search-group .input-group-text i {
    font-size: 13px;
}

.sms-templates-page .sms-search-group .form-control {
    min-height: 39px;
    border: 1px solid #e6e8eb;
    border-left: 0;
    border-radius: 0 6px 6px 0;
    color: var(--sms-text);
    font-size: 13px;
    box-shadow: none;
}

.sms-templates-page .sms-search-group .form-control::placeholder {
    color: #a1a7ad;
    opacity: 1;
}

.sms-templates-page .sms-form-control {
    min-height: 39px;
    border: 1px solid #e6e8eb;
    border-radius: 6px;
    color: #59616a;
    background-color: #fff;
    font-size: 13px;
    box-shadow: none;
}

.sms-templates-page .sms-search-group .form-control:focus,
.sms-templates-page .sms-search-group .input-group-text:focus-within,
.sms-templates-page .sms-form-control:focus {
    border-color: rgba(21, 114, 232, 0.55);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.08);
    z-index: 2;
}

.sms-templates-page .sms-btn-light,
.sms-templates-page .sms-btn-filter {
    min-height: 38px;
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.sms-templates-page .sms-btn-light {
    color: #68717a;
    background: #f5f6f8;
    border: 1px solid #e9ebef;
}

.sms-templates-page .sms-btn-light:hover {
    background: #eceff3;
    color: #343a40;
}

.sms-templates-page .sms-btn-filter {
    background: #6861ce;
    border-color: #6861ce;
    color: #fff;
}

.sms-templates-page .sms-btn-filter:hover {
    background: #554dbd;
    border-color: #554dbd;
}

/* Table Header */
.sms-templates-page .sms-table-heading {
    padding: 17px 20px;
    border-bottom: 1px solid var(--sms-border);
}

.sms-templates-page .sms-section-title {
    color: var(--sms-text);
    font-size: 14px;
    font-weight: 700;
}

.sms-templates-page .sms-section-subtitle {
    color: var(--sms-muted);
    font-size: 12px;
    line-height: 1.5;
}

.sms-templates-page .sms-table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.sms-templates-page .sms-template-table {
    width: 100%;
    min-width: 760px;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
}

.sms-templates-page .sms-template-table thead th {
    padding: 13px 14px;
    background: #f8f9fc;
    color: #8d9498;
    border-bottom: 1px solid var(--sms-border);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.55px;
    text-transform: uppercase;
    white-space: nowrap;
    vertical-align: middle;
}

.sms-templates-page .sms-template-table thead th:first-child {
    padding-left: 20px;
}

.sms-templates-page .sms-template-table thead th:last-child {
    padding-right: 20px;
}

.sms-templates-page .sms-template-table tbody td {
    padding: 14px;
    color: #59616a;
    border-bottom: 1px solid #f0f1f4;
    font-size: 12px;
    vertical-align: middle;
    overflow-wrap: anywhere;
}

.sms-templates-page .sms-template-table tbody tr:last-child td {
    border-bottom: 0;
}

.sms-templates-page .sms-template-table tbody tr:hover {
    background-color: #fafbfe;
}

.sms-templates-page .sms-template-table tbody td:first-child {
    padding-left: 20px;
}

.sms-templates-page .sms-template-table tbody td:last-child {
    padding-right: 20px;
}

.sms-templates-page .sms-template-column {
    width: 32%;
}

.sms-templates-page .sms-message-column {
    width: 20%;
}

.sms-templates-page .sms-format-column {
    width: 18%;
}

.sms-templates-page .sms-status-column {
    width: 14%;
}

.sms-templates-page .sms-actions-column {
    width: 16%;
}

/* Table Content - Partial Compatibility */
.sms-templates-page .sms-template-table .badge {
    border-radius: 5px;
    padding: 5px 8px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.1px;
}

.sms-templates-page .sms-template-table .btn {
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
}

.sms-templates-page .sms-template-table .btn-sm {
    padding: 5px 9px;
}

.sms-templates-page .sms-template-table .form-check {
    min-height: auto;
    margin-bottom: 0;
}

.sms-templates-page .sms-template-table .form-check-input {
    cursor: pointer;
}

/* Pagination */
.sms-templates-page .sms-pagination-footer {
    padding: 15px 20px;
    background: #fff;
    border-top: 1px solid var(--sms-border);
}

.sms-templates-page .sms-entries-text {
    color: var(--sms-muted);
    font-size: 12px;
    line-height: 1.5;
}

.sms-templates-page #ajaxPaginationContainer {
    max-width: 100%;
    overflow-x: auto;
}

.sms-templates-page .pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin: 0;
}

.sms-templates-page .pagination .page-link {
    min-width: 31px;
    min-height: 31px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 5px 9px;
    border: 1px solid #e7e9ef;
    border-radius: 5px !important;
    background: #fff;
    color: #68717a;
    font-size: 11px;
    box-shadow: none;
}

.sms-templates-page .pagination .page-link:hover {
    background: var(--sms-primary-soft);
    border-color: #d8e6ff;
    color: var(--sms-primary);
}

.sms-templates-page .pagination .page-item.active .page-link {
    background: var(--sms-primary);
    border-color: var(--sms-primary);
    color: #fff;
}

.sms-templates-page .pagination .page-item.disabled .page-link {
    color: #b5bbc2;
    background: #f8f9fa;
}

/* Modals */
.sms-app-modal .sms-modal-content {
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.04);
    border-radius: 9px;
    background: #fff;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.14);
}

.sms-app-modal .sms-modal-header {
    padding: 17px 20px;
    border-bottom: 1px solid #ebedf2;
    background: #fff;
}

.sms-app-modal .sms-modal-title {
    color: #2a2f5b;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.4;
}

.sms-app-modal .sms-modal-key {
    display: inline-block;
    color: #8d9498;
    font-size: 11px;
}

.sms-app-modal .sms-modal-body {
    padding: 20px;
}

.sms-app-modal .sms-modal-label {
    display: block;
    margin-bottom: 8px;
    color: #68717a;
    font-size: 12px;
    font-weight: 600;
}

.sms-app-modal .sms-message-preview {
    min-height: 90px;
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
    padding: 14px;
    border: 1px solid #e9ebef;
    border-radius: 6px;
    background: #f8f9fc;
    color: #424957;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.7;
}

.sms-app-modal .sms-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #ebedf2;
    background: #f8f9fc;
}

.sms-app-modal .sms-modal-close-btn {
    min-width: 85px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
}

.sms-app-modal #previewModalVars .badge {
    padding: 6px 9px;
    border: 1px solid #e6e8ee !important;
    border-radius: 5px;
    background: #f8f9fc !important;
    color: #59616a !important;
    font-size: 11px !important;
}

/* Alert & Delete Modals */
.sms-app-modal .sms-alert-modal-content {
    border-radius: 10px;
}

.sms-app-modal .sms-alert-modal-body {
    padding: 26px 22px;
    text-align: center;
}

.sms-app-modal .sms-alert-icon {
    min-height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sms-app-modal .sms-alert-icon i,
.sms-app-modal .sms-delete-icon i {
    font-size: 38px;
}

.sms-app-modal .sms-alert-message {
    color: #858d96;
    font-size: 12px;
    line-height: 1.65;
    overflow-wrap: anywhere;
}

.sms-app-modal .sms-alert-confirm-btn {
    min-height: 36px;
    width: 100%;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
}

.sms-app-modal .sms-delete-icon {
    color: #f25961;
}

.sms-app-modal .sms-modal-cancel-btn,
.sms-app-modal .sms-modal-delete-btn {
    min-height: 36px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
}

/* Modal Backdrop */
.modal-backdrop.show {
    opacity: 0.45;
}

/* Responsive Layout */
@media (max-width: 767.98px) {
    .sms-templates-page {
        padding-top: 18px !important;
        padding-bottom: 22px !important;
    }

    .sms-templates-page .sms-page-header {
        margin-bottom: 20px !important;
    }

    .sms-templates-page .sms-page-title {
        font-size: 19px;
    }

    .sms-templates-page .sms-page-subtitle {
        max-width: 100%;
        font-size: 12px;
    }

    .sms-templates-page .sms-btn-primary {
        width: 100%;
        min-height: 40px;
    }

    .sms-templates-page .sms-filter-body {
        padding: 14px;
    }

    .sms-templates-page .sms-table-heading {
        padding: 14px;
    }

    .sms-templates-page .sms-template-table {
        min-width: 760px;
    }

    .sms-templates-page .sms-pagination-footer {
        padding: 14px;
    }

    .sms-templates-page .sms-pagination-footer > div {
        align-items: flex-start !important;
    }

    .sms-templates-page #ajaxPaginationContainer {
        width: 100%;
    }

    .sms-templates-page .pagination {
        justify-content: flex-start;
    }
}

@media (max-width: 575.98px) {
    .sms-templates-page .sms-btn-light,
    .sms-templates-page .sms-btn-filter {
        flex: 1;
        justify-content: center;
    }

    .sms-templates-page .sms-search-group .form-control {
        font-size: 12px;
    }

    .sms-templates-page .sms-entries-text {
        font-size: 11px;
    }

    .sms-app-modal .modal-dialog {
        margin: 12px;
    }

    .sms-app-modal .sms-modal-header,
    .sms-app-modal .sms-modal-body {
        padding: 15px;
    }

    .sms-app-modal .sms-alert-modal-body {
        padding: 22px 17px;
    }
}

@media (max-width: 360px) {
    .sms-templates-page .sms-page-title {
        font-size: 18px;
    }

    .sms-templates-page .sms-filter-body {
        padding: 11px;
    }

    .sms-templates-page .sms-btn-light,
    .sms-templates-page .sms-btn-filter {
        padding-left: 9px;
        padding-right: 9px;
        font-size: 11px;
    }
}
</style>
@endsection

@section('content')
    @yield('admin_content')
@endsection