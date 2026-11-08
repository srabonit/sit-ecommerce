
@extends('layouts.admin')

@section('title', 'SMS Delivery Logs & DLR')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4 sms-logs-page">

    {{-- Top Header & Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 bg-white p-3 border rounded-3 shadow-xs">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <div class="fs-3 text-primary">
            <i class="fas fa-comments"></i>
        </div>
        <div class="min-w-0">
            <h5 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 16px;">SMS Logs</h5>
            <small class="text-secondary d-block mt-0 text-truncate" style="font-size: 11.5px; line-height: 1.35;">Delivery Logs &amp; Gateway History</small>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
        <button type="button" id="btnBulkDelete" class="btn btn-outline-danger btn-compact px-3 d-none action-btn shadow-xs flex-fill flex-md-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5" style="height: 34px; font-size: 11.5px; font-weight: 600;">
            <i class="fas fa-trash-alt"></i>
            <span>Delete Selected (<span id="selectedCount">0</span>)</span>
        </button>

        @php
            $exportUrl = Route::has('admin.sms.logs.export') ? route('admin.sms.logs.export', request()->query()) : url('admin/sms/logs/export?' . http_build_query(request()->query()));
        @endphp

        <a href="{{ $exportUrl }}" class="btn btn-outline-secondary btn-compact px-3 action-btn shadow-xs flex-fill flex-md-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap" style="height: 34px; font-size: 11.5px; font-weight: 600;">
            <i class="fas fa-file-csv"></i>
            <span>Export CSV</span>
        </a>
    </div>
</div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <span class="kpi-label">Dispatched</span>
                            <h5 class="kpi-value text-dark">{{ number_format($stats['total_dispatched'] ?? ($stats['total_sent'] ?? 0)) }}</h5>
                            <span class="kpi-description">Total attempts</span>
                        </div>
                        <span class="kpi-icon icon-blue">
                            <i class="fas fa-paper-plane"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <span class="kpi-label">Sent</span>
                            <h5 class="kpi-value text-success">{{ number_format($stats['total_sent'] ?? 0) }}</h5>
                            <span class="kpi-description">Processed by gateway</span>
                        </div>
                        <span class="kpi-icon icon-green">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <span class="kpi-label">Failed / Blocked</span>
                            <h5 class="kpi-value text-danger">{{ number_format($stats['total_failed'] ?? 0) }}</h5>
                            <span class="kpi-description">Undelivered or restricted</span>
                        </div>
                        <span class="kpi-icon icon-red">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <span class="kpi-label">Total Spend</span>
                            <h5 class="kpi-value text-primary">৳ {{ number_format((float) ($stats['total_cost'] ?? 0), 2) }}</h5>
                            <span class="kpi-description">Billed usage</span>
                        </div>
                        <span class="kpi-icon icon-purple">
                            <i class="fas fa-wallet"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filter Bar --}}
    @php
        $indexUrl = Route::has('admin.sms.logs.index') ? route('admin.sms.logs.index') : url('admin/sms/logs');
    @endphp

    <div class="card logs-card mb-3">
        <div class="card-header logs-card-header">
            <div class="d-flex align-items-center gap-2">
                <span class="section-icon">
                    <i class="fas fa-filter"></i>
                </span>
                <div>
                    <h6 class="fw-bold mb-0">Filter SMS Logs</h6>
                    <small class="text-muted">Search and filter your SMS delivery records.</small>
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <form action="{{ $indexUrl }}" method="GET" class="row g-2 align-items-center">

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="input-group input-group-sm search-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Phone or message ID..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <select name="sms_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="transactional" {{ request('sms_type') === 'transactional' ? 'selected' : '' }}>Transactional</option>
                        <option value="otp" {{ request('sms_type') === 'otp' ? 'selected' : '' }}>OTP Code</option>
                        <option value="marketing" {{ request('sms_type') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <input
                        type="date"
                        name="start_date"
                        class="form-control form-control-sm"
                        value="{{ request('start_date') }}"
                        title="Start Date"
                    >
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <input
                        type="date"
                        name="end_date"
                        class="form-control form-control-sm"
                        value="{{ request('end_date') }}"
                        title="End Date"
                    >
                </div>

                <div class="col-12 col-md-3 col-lg-1 d-flex justify-content-end gap-2">
                    @if(request()->hasAny(['search', 'status', 'sms_type', 'start_date', 'end_date']))
                        <a href="{{ $indexUrl }}" class="btn btn-light btn-sm filter-reset-btn" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif

                    <button type="submit" class="btn btn-secondary btn-sm filter-submit-btn">Filter</button>
                </div>

            </form>
        </div>
    </div>

    {{-- Logs Table Card --}}
    <div class="card logs-card overflow-hidden">

        <div class="card-header logs-card-header table-heading">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="section-icon">
                        <i class="fas fa-list-alt"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0">Delivery Records</h6>
                        <small class="text-muted">SMS dispatch history and delivery status.</small>
                    </div>
                </div>

                <span class="record-label">
                    <i class="fas fa-database me-1"></i>
                    {{ $logs->total() }} Records
                </span>
            </div>
        </div>

        <div class="table-responsive logs-table-wrap">
            <table class="table align-middle table-hover mb-0 logs-table">
                <thead>
                    <tr>
                        <th class="ps-3 select-column">
                            <input class="form-check-input" type="checkbox" id="selectAllLogs" style="cursor: pointer; border-radius: 4px;">
                        </th>
                        <th>Recipient</th>
                        <th class="message-column">Message Preview</th>
                        <th>Type</th>
                        <th>Credits / Cost</th>
                        <th>Status</th>
                        <th>Dispatched At</th>
                        <th class="pe-3 text-end actions-column">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        @php
                            $deleteUrl = Route::has('admin.sms.logs.destroy') ? route('admin.sms.logs.destroy', $log->id) : url('admin/sms/logs/' . $log->id);
                            $showUrl   = Route::has('admin.sms.logs.show') ? route('admin.sms.logs.show', $log->id) : url('admin/sms/logs/' . $log->id);
                            $resendUrl = Route::has('admin.sms.logs.resend') ? route('admin.sms.logs.resend', $log->id) : url('admin/sms/logs/' . $log->id . '/resend');
                        @endphp

                        <tr>
                            {{-- Checkbox --}}
                            <td class="ps-3">
                                <input class="form-check-input log-item-check" type="checkbox" value="{{ $log->id }}" style="cursor: pointer; border-radius: 4px;">
                            </td>

                            {{-- Recipient & Masking --}}
                            <td>
                                <div class="recipient-number font-monospace">{{ $log->recipient }}</div>
                                <div class="recipient-meta">
                                    <span>Mask: {{ $log->sender_id ?: 'Default' }}</span>

                                    @if($log->is_sandbox)
                                        <span class="badge sandbox-badge">Sandbox</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Message Content --}}
                            <td>
                                <div class="message-preview font-monospace" title="{{ $log->message_content }}">
                                    {{ $log->message_content }}
                                </div>

                                @if($log->message_id)
                                    <code class="message-id">ID: {{ $log->message_id }}</code>
                                @endif
                            </td>

                            {{-- Type --}}
                            <td>
                                @php
                                    $typeBadges = [
                                        'transactional' => 'bg-info-subtle text-info border-info-subtle',
                                        'otp'           => 'bg-primary-subtle text-primary border-primary-subtle',
                                        'marketing'     => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    ];
                                @endphp

                                <span class="badge border type-badge {{ $typeBadges[$log->sms_type] ?? 'bg-light text-secondary border' }}">
                                    {{ ucfirst($log->sms_type) }}
                                </span>
                            </td>

                            {{-- Credits & Cost --}}
                            <td>
                                <div class="credits-main">{{ $log->sms_parts }} Part ({{ $log->characters_count }}c)</div>
                                <div class="credits-cost">৳ {{ number_format((float) $log->cost, 4) }}</div>
                            </td>

                            {{-- Status Badge --}}
                            <td>
                                @php
                                    $statusBadges = [
                                        'delivered' => 'bg-success-subtle text-success border-success-subtle',
                                        'sent'      => 'bg-info-subtle text-info border-info-subtle',
                                        'failed'    => 'bg-danger-subtle text-danger border-danger-subtle',
                                        'blocked'   => 'bg-secondary-subtle text-secondary border',
                                    ];
                                @endphp

                                <span class="badge border status-badge {{ $statusBadges[$log->status] ?? 'bg-light text-muted border' }}" id="statusBadge-{{ $log->id }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>

                            {{-- Time --}}
                            <td>
                                <div class="date-main">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="date-time">{{ $log->created_at->format('h:i:s A') }}</div>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">

                                    {{-- Inspect Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm action-icon-btn btn-inspect"
                                        data-url="{{ $showUrl }}"
                                        title="Inspect Details"
                                    >
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>

                                    {{-- Resend Button --}}
                                    @if(in_array($log->status, ['failed', 'blocked']))
                                        <form action="{{ $resendUrl }}" method="POST" class="d-inline" onsubmit="return confirm('Resend SMS to {{ $log->recipient }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-sm action-icon-btn" title="Resend SMS">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Single Delete Button --}}
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm action-icon-btn btn-delete-single"
                                        data-action="{{ $deleteUrl }}"
                                        data-id="{{ $log->id }}"
                                        title="Delete Log"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-comment-slash"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">No SMS Logs Found</h6>
                                <p class="text-muted small mb-0">There are no records matching your current filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="card-footer logs-card-footer">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <span class="pagination-summary">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                    </span>

                    <div class="pagination-wrap">
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>

{{-- 1. Inspection Modal --}}
<div class="modal fade sms-log-modal" id="logInspectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title fw-bold text-dark mb-1" id="inspectModalTitle">SMS Log Details</h6>
                    <small class="text-muted">Detailed gateway and delivery information.</small>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeLogModal('logInspectModal')"></button>
            </div>

            <div class="modal-body p-3 p-md-4">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="modal-info-box">
                            <span>Recipient</span>
                            <strong class="font-monospace" id="mRecipient">-</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="modal-info-box">
                            <span>Gateway Status</span>
                            <strong class="text-primary" id="mStatus">-</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="modal-info-box">
                            <span>Parts / Characters</span>
                            <strong id="mParts">-</strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="modal-info-box">
                            <span>Total Cost</span>
                            <strong id="mCost">-</strong>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="modal-section-label">Message Body</label>
                    <div class="modal-message-body" id="mBody"></div>
                </div>

                <div>
                    <label class="modal-section-label">Gateway API Response Payload</label>
                    <pre class="modal-response-payload" id="mRaw"></pre>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeLogModal('logInspectModal')">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- 2. Single Delete Confirmation Modal --}}
<div class="modal fade sms-log-modal" id="singleDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center">
            <div class="modal-body p-4">
                <div class="confirmation-icon danger-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2">Delete Log Record?</h6>
                <p class="text-muted small mb-4">Are you sure you want to delete this SMS audit record?</p>

                <form id="singleDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm flex-fill" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeLogModal('singleDeleteModal')">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm flex-fill">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 3. Bulk Delete Confirmation Modal --}}
@php
    $bulkDeleteAction = Route::has('admin.sms.logs.bulk_delete') ? route('admin.sms.logs.bulk_delete') : url('admin/sms/logs/bulk-delete');
@endphp

<div class="modal fade sms-log-modal" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center">
            <div class="modal-body p-4">
                <div class="confirmation-icon danger-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2">Delete Selected Logs?</h6>
                <p class="text-muted small mb-4">
                    Permanently delete <strong id="bulkDeleteCountText">0</strong> selected SMS log record(s)?
                </p>

                <form id="bulkDeleteForm" action="{{ $bulkDeleteAction }}" method="POST">
                    @csrf

                    <div id="bulkDeleteInputsContainer"></div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm flex-fill" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeLogModal('bulkDeleteModal')">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm flex-fill">Delete All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Direct In-Content JavaScript Engine --}}
<script>
function openLogModal(modalId) {
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

function closeLogModal(modalId) {
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

(function() {
    function initLogsModule() {
        const selectAllLogs   = document.getElementById('selectAllLogs');
        const itemCheckboxes  = document.querySelectorAll('.log-item-check');
        const btnBulkDelete   = document.getElementById('btnBulkDelete');
        const selectedCount   = document.getElementById('selectedCount');

        // 1. Sync Select All Checkbox and Bulk Delete Action Button
        function syncSelectionState() {
            const checkedBoxes = document.querySelectorAll('.log-item-check:checked');
            const count = checkedBoxes.length;

            if (selectedCount) selectedCount.innerText = count;

            if (btnBulkDelete) {
                if (count > 0) {
                    btnBulkDelete.classList.remove('d-none');
                } else {
                    btnBulkDelete.classList.add('d-none');
                }
            }

            if (selectAllLogs && itemCheckboxes.length > 0) {
                selectAllLogs.checked = (count === itemCheckboxes.length);
            }
        }

        if (selectAllLogs) {
            selectAllLogs.addEventListener('change', function() {
                const isChecked = this.checked;
                itemCheckboxes.forEach(cb => cb.checked = isChecked);
                syncSelectionState();
            });
        }

        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', syncSelectionState);
        });

        // 2. Bulk Delete Modal Trigger
        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', function() {
                const checkedBoxes = document.querySelectorAll('.log-item-check:checked');
                if (checkedBoxes.length === 0) return;

                const container = document.getElementById('bulkDeleteInputsContainer');
                container.innerHTML = '';

                checkedBoxes.forEach(cb => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = cb.value;
                    container.appendChild(hidden);
                });

                document.getElementById('bulkDeleteCountText').innerText = checkedBoxes.length;
                openLogModal('bulkDeleteModal');
            });
        }

        // 3. Global Event Delegation for Row Actions (Inspect & Single Delete)
        document.addEventListener('click', function(e) {
            // Inspect Payload Details
            const inspectBtn = e.target.closest('.btn-inspect');
            if (inspectBtn) {
                e.preventDefault();
                const url = inspectBtn.getAttribute('data-url');

                fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const data = res.data;
                        document.getElementById('inspectModalTitle').innerText = `Log #${data.id} - ${data.recipient}`;
                        document.getElementById('mRecipient').innerText = data.recipient;
                        document.getElementById('mStatus').innerText = (data.status || '').toUpperCase();
                        document.getElementById('mParts').innerText = `${data.sms_parts} Part(s) (${data.characters_count}c)`;
                        document.getElementById('mCost').innerText = data.cost;
                        document.getElementById('mBody').innerText = data.message_content;
                        document.getElementById('mRaw').innerText = JSON.stringify(data.gateway_response, null, 2);

                        openLogModal('logInspectModal');
                    } else {
                        alert('Could not retrieve log details.');
                    }
                })
                .catch(() => alert('Network error retrieving log details.'));
                return;
            }

            // Single Delete
            const deleteBtn = e.target.closest('.btn-delete-single');
            if (deleteBtn) {
                e.preventDefault();
                const action = deleteBtn.getAttribute('data-action');
                document.getElementById('singleDeleteForm').setAttribute('action', action);
                openLogModal('singleDeleteModal');
                return;
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLogsModule);
    } else {
        initLogsModule();
    }
})();
</script>

<style>
.sms-logs-page {
    --logs-primary: #1572e8;
    --logs-ink: #1f2937;
    --logs-muted: #6b7280;
    --logs-border: #e5e7eb;
    --logs-soft: #f7f9fc;
    --logs-radius: 10px;
    min-width: 0;
}

.sms-logs-page .page-title {
    font-size: clamp(1.15rem, 2vw, 1.4rem);
    letter-spacing: -.3px;
}

.sms-logs-page .action-btn {
    border-radius: 7px;
    font-weight: 600;
    min-height: 34px;
}

.sms-logs-page .kpi-card,
.sms-logs-page .logs-card {
    border: 1px solid var(--logs-border) !important;
    border-radius: var(--logs-radius);
    background: #fff;
    box-shadow: 0 2px 10px rgba(31, 41, 55, .035);
    overflow: hidden;
}

.sms-logs-page .kpi-card .card-body {
    padding: 17px;
}

.sms-logs-page .kpi-label {
    display: block;
    color: var(--logs-muted);
    font-size: 11px;
    font-weight: 600;
}

.sms-logs-page .kpi-value {
    margin: 8px 0 3px;
    font-size: clamp(18px, 2vw, 23px);
    font-weight: 750;
    letter-spacing: -.5px;
    overflow-wrap: anywhere;
}

.sms-logs-page .kpi-description {
    display: block;
    color: #929baa;
    font-size: 10px;
}

.sms-logs-page .kpi-icon {
    width: 37px;
    height: 37px;
    flex: 0 0 37px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    font-size: 14px;
}

.sms-logs-page .icon-blue {
    color: #1572e8;
    background: #edf4ff;
}

.sms-logs-page .icon-green {
    color: #15966c;
    background: #eaf8f1;
}

.sms-logs-page .icon-red {
    color: #dc3545;
    background: #fff0f1;
}

.sms-logs-page .icon-purple {
    color: #7952c7;
    background: #f3efff;
}

.sms-logs-page .logs-card-header {
    display: flex;
    align-items: center;
    background: #fff;
    border-bottom: 1px solid var(--logs-border);
    padding: 14px 18px;
}

.sms-logs-page .logs-card-header h6 {
    color: var(--logs-ink);
    font-size: 13px;
}

.sms-logs-page .logs-card-header small {
    font-size: 10px;
}

.sms-logs-page .section-icon {
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: var(--logs-primary);
    background: #edf4ff;
    font-size: 13px;
}

.sms-logs-page .form-control,
.sms-logs-page .form-select {
    min-height: 36px;
    border: 1px solid #dfe3e9;
    border-radius: 7px;
    color: var(--logs-ink);
    font-size: 12px;
    box-shadow: none;
}

.sms-logs-page .form-control:focus,
.sms-logs-page .form-select:focus {
    border-color: var(--logs-primary);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
}

.sms-logs-page .search-group .input-group-text {
    border: 1px solid #dfe3e9;
    border-right: 0;
    border-radius: 7px 0 0 7px;
    background: #fff;
    color: #98a2b3;
}

.sms-logs-page .search-group .form-control {
    border-left: 0;
    border-radius: 0 7px 7px 0;
}

.sms-logs-page .filter-submit-btn {
    min-height: 36px;
    padding: 0 13px;
    border-radius: 7px;
    font-weight: 600;
    background: #475467;
    border-color: #475467;
}

.sms-logs-page .filter-reset-btn {
    min-height: 36px;
    min-width: 36px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
}

.sms-logs-page .record-label {
    padding: 6px 10px;
    border-radius: 6px;
    background: #f3f5f8;
    color: #667085;
    font-size: 10px;
    font-weight: 600;
}

.sms-logs-page .logs-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.sms-logs-page .logs-table {
    min-width: 1050px;
    color: #344054;
}

.sms-logs-page .logs-table thead th {
    padding: 13px 11px;
    background: #f8fafc;
    border-bottom: 1px solid #e7ebf0;
    color: #667085;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .35px;
    text-transform: uppercase;
    white-space: nowrap;
}

.sms-logs-page .logs-table tbody td {
    padding: 13px 11px;
    border-color: #edf0f4;
    vertical-align: middle;
    font-size: 11px;
}

.sms-logs-page .logs-table tbody tr:last-child td {
    border-bottom: 0;
}

.sms-logs-page .logs-table tbody tr:hover {
    background: #fafcff;
}

.sms-logs-page .select-column {
    width: 42px;
}

.sms-logs-page .message-column {
    min-width: 240px;
}

.sms-logs-page .actions-column {
    width: 130px;
}

.sms-logs-page .form-check-input {
    cursor: pointer;
    border-color: #cbd2dc;
    box-shadow: none;
}

.sms-logs-page .form-check-input:checked {
    background-color: var(--logs-primary);
    border-color: var(--logs-primary);
}

.sms-logs-page .recipient-number {
    color: #263244;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.sms-logs-page .recipient-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 4px;
    color: #8a94a3;
    font-size: 10px;
}

.sms-logs-page .sandbox-badge {
    padding: 3px 5px;
    border: 1px solid #f5d98c;
    border-radius: 4px;
    background: #fff8e6;
    color: #956800;
    font-size: 9px;
}

.sms-logs-page .message-preview {
    max-width: 320px;
    overflow: hidden;
    color: #667085;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sms-logs-page .message-id {
    display: inline-block;
    margin-top: 4px;
    color: #8a94a3;
    font-size: 9px;
}

.sms-logs-page .type-badge,
.sms-logs-page .status-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 5px;
    padding: 5px 7px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}

.sms-logs-page .credits-main {
    color: #344054;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.sms-logs-page .credits-cost {
    margin-top: 4px;
    color: #8a94a3;
    font-size: 10px;
    white-space: nowrap;
}

.sms-logs-page .date-main {
    color: #344054;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.sms-logs-page .date-time {
    margin-top: 4px;
    color: #8a94a3;
    font-size: 10px;
    white-space: nowrap;
}

.sms-logs-page .action-icon-btn {
    width: 30px;
    height: 29px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 6px;
    font-size: 11px;
}

.sms-logs-page .action-icon-btn:hover {
    transform: translateY(-1px);
}

.sms-logs-page .empty-state {
    padding: 50px 15px !important;
    text-align: center;
    color: var(--logs-muted);
}

.sms-logs-page .empty-state-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 13px;
    border-radius: 14px;
    background: #f1f4f8;
    color: #98a2b3;
    font-size: 22px;
}

.sms-logs-page .logs-card-footer {
    padding: 14px 18px;
    border-top: 1px solid var(--logs-border);
    background: #fff;
}

.sms-logs-page .pagination-summary {
    color: #8a94a3;
    font-size: 11px;
}

.sms-logs-page .pagination-wrap {
    max-width: 100%;
    overflow-x: auto;
}

.sms-logs-page .pagination {
    margin-bottom: 0;
    flex-wrap: nowrap;
}

.sms-logs-page .pagination .page-link {
    border-radius: 5px;
    margin: 0 2px;
    font-size: 11px;
}

/* Modal Styling */
.sms-log-modal .modal-content {
    overflow: hidden;
    border-radius: 12px;
}

.sms-log-modal .modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e9edf2;
    background: #fff;
}

.sms-log-modal .modal-title {
    font-size: 14px;
}

.sms-log-modal .modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e9edf2;
    background: #f8fafc;
}

.sms-log-modal .modal-info-box {
    height: 100%;
    padding: 11px;
    border: 1px solid #e8edf3;
    border-radius: 8px;
    background: #fafbfc;
}

.sms-log-modal .modal-info-box span {
    display: block;
    margin-bottom: 6px;
    color: #8a94a3;
    font-size: 10px;
}

.sms-log-modal .modal-info-box strong {
    display: block;
    color: #344054;
    font-size: 11px;
    overflow-wrap: anywhere;
}

.sms-log-modal .modal-section-label {
    display: block;
    margin-bottom: 8px;
    color: #667085;
    font-size: 11px;
    font-weight: 700;
}

.sms-log-modal .modal-message-body {
    padding: 14px;
    min-height: 65px;
    border: 1px solid #e5eaf0;
    border-radius: 8px;
    background: #f8fafc;
    color: #344054;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.7;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.sms-log-modal .modal-response-payload {
    max-height: 220px;
    overflow: auto;
    padding: 14px;
    margin: 0;
    border-radius: 8px;
    background: #202938;
    color: #e5eaf2;
    font-size: 10px;
    line-height: 1.6;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.sms-log-modal .confirmation-icon {
    width: 58px;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 17px;
    border-radius: 50%;
    font-size: 24px;
}

.sms-log-modal .danger-icon {
    background: #fff0f1;
    color: #dc3545;
}

.sms-log-modal .btn {
    border-radius: 7px;
    font-weight: 600;
}

.sms-logs-page .alert {
    border-radius: 8px;
}

@media (max-width: 767.98px) {
    .sms-logs-page .page-title {
        font-size: 19px;
    }

    .sms-logs-page .kpi-card .card-body {
        padding: 13px;
    }

    .sms-logs-page .kpi-icon {
        width: 32px;
        height: 32px;
        flex-basis: 32px;
        font-size: 12px;
    }

    .sms-logs-page .logs-card-header {
        padding: 13px;
    }

    .sms-logs-page .logs-card-footer {
        padding: 13px;
    }

    .sms-logs-page .pagination-summary {
        width: 100%;
    }

    .sms-logs-page .pagination-wrap {
        width: 100%;
    }

    .sms-logs-page .pagination {
        justify-content: flex-start;
    }

    .sms-log-modal .modal-body {
        padding: 15px !important;
    }
}

@media (max-width: 420px) {
    .sms-logs-page .kpi-value {
        font-size: 17px;
    }

    .sms-logs-page .kpi-description {
        font-size: 9px;
    }

    .sms-logs-page .kpi-label {
        font-size: 10px;
    }

    .sms-logs-page .action-btn {
        font-size: 11px;
    }

    .sms-logs-page .record-label {
        font-size: 9px;
    }
}
</style>
@endsection

@section('content')
    @yield('admin_content')
@endsection
