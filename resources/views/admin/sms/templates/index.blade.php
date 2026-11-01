@extends('layouts.admin')

@section('title', 'SMS Templates')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">SMS Templates</h4>
            <p class="text-muted small mb-0">Manage transactional, order, and marketing message templates.</p>
        </div>
        <a href="{{ route('admin.sms.templates.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> New Template
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.templates.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name, key, or text..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>Orders</option>
                        <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>Auth & OTP</option>
                        <option value="marketing" {{ request('type') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                    </select>
                </div>

                <div class="col-6 col-md-4 text-end">
                    @if(request()->hasAny(['search', 'type']))
                        <a href="{{ route('admin.sms.templates.index') }}" class="btn btn-light btn-sm px-2 text-muted me-1">
                            Clear
                        </a>
                    @endif
                    <button type="submit" class="btn btn-secondary btn-sm px-3">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                        <th class="ps-3 py-3">Template Name</th>
                        <th style="min-width: 250px;">Message Body</th>
                        <th>Category</th>
                        <th>Parts</th>
                        <th>Status</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($templates as $template)
                        <tr>
                            {{-- Name & Key --}}
                            <td class="ps-3 py-3">
                                <div class="fw-semibold text-dark">{{ $template->name }}</div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    <code class="text-muted" style="font-size: 11px;">{{ $template->key }}</code>
                                    @if($template->is_system)
                                        <span class="badge bg-secondary-subtle text-secondary border px-1" style="font-size: 10px;">System</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Message Body Preview --}}
                            <td>
                                <p class="text-muted mb-1 text-truncate" style="max-width: 320px;" title="{{ $template->body }}">
                                    {{ $template->body }}
                                </p>
                                @if(!empty($template->variables))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(array_slice((array) $template->variables, 0, 3) as $var)
                                            <span class="badge bg-light text-dark border px-1 py-0" style="font-size: 10px;">{{ '{' . $var . '}' }}</span>
                                        @endforeach
                                        @if(count((array) $template->variables) > 3)
                                            <span class="text-muted" style="font-size: 10px;">+{{ count((array) $template->variables) - 3 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Category Badge --}}
                            <td>
                                @php
                                    $typeStyles = [
                                        'order' => 'bg-info-subtle text-info border-info-subtle',
                                        'auth' => 'bg-primary-subtle text-primary border-primary-subtle',
                                        'marketing' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                        'general' => 'bg-light text-secondary border',
                                    ];
                                @endphp
                                <span class="badge border {{ $typeStyles[$template->event_type] ?? 'bg-light text-secondary border' }}">
                                    {{ ucfirst($template->event_type) }}
                                </span>
                            </td>

                            {{-- Metrics / Parts --}}
                            <td>
                                <span class="fw-medium text-dark">{{ $template->estimated_parts ?? 1 }} Part</span>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $template->is_unicode ? 'Unicode' : 'GSM-7' }}
                                </div>
                            </td>

                            {{-- Status Toggle --}}
                            <td>
                                <div class="form-check form-switch m-0">
                                    <input 
                                        class="form-check-input status-toggle" 
                                        type="checkbox" 
                                        role="switch"
                                        data-id="{{ $template->id }}"
                                        data-url="{{ route('admin.sms.templates.toggle_status', $template->id) }}"
                                        {{ $template->is_active ? 'checked' : '' }}
                                    >
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-light btn-preview" 
                                        data-name="{{ $template->name }}"
                                        data-body="{{ $template->body }}"
                                        data-vars="{{ json_encode($template->variables ?? []) }}"
                                        title="Preview"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.sms.templates.edit', $template->id) }}" class="btn btn-light" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(!$template->is_system)
                                        <form action="{{ route('admin.sms.templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this template?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light text-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-text fs-3 d-block mb-2 text-secondary"></i>
                                No templates found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} templates
                    </span>
                    {{ $templates->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="templatePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold" id="previewModalTitle">Template Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <label class="form-label small text-muted mb-1">Message Content</label>
                <div class="p-3 bg-light rounded border text-dark font-monospace small mb-3" id="previewModalBody" style="white-space: pre-wrap; word-break: break-word;"></div>

                <label class="form-label small text-muted mb-1">Available Tags</label>
                <div id="previewModalVars" class="d-flex flex-wrap gap-1"></div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. AJAX Status Toggle
    document.querySelectorAll('.status-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const url = this.getAttribute('data-url');
            const isChecked = this.checked;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    toggle.checked = !isChecked;
                    alert(data.message || 'Failed to update status.');
                }
            })
            .catch(() => {
                toggle.checked = !isChecked;
                alert('Connection error while updating status.');
            });
        });
    });

    // 2. Quick Preview Modal Trigger
    const previewModalEl = document.getElementById('templatePreviewModal');
    const previewModal = new bootstrap.Modal(previewModalEl);
    const modalTitle = document.getElementById('previewModalTitle');
    const modalBody = document.getElementById('previewModalBody');
    const modalVars = document.getElementById('previewModalVars');

    document.querySelectorAll('.btn-preview').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const name = this.getAttribute('data-name');
            const body = this.getAttribute('data-body');
            const rawVars = this.getAttribute('data-vars');
            let vars = [];

            try {
                vars = JSON.parse(rawVars);
            } catch (e) {
                vars = [];
            }

            modalTitle.textContent = name;
            modalBody.textContent = body;
            modalVars.innerHTML = '';

            if (Array.isArray(vars) && vars.length > 0) {
                vars.forEach(v => {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-secondary-subtle text-dark border px-2 py-1';
                    badge.style.fontSize = '11px';
                    badge.textContent = `{${v}}`;
                    modalVars.appendChild(badge);
                });
            } else {
                modalVars.innerHTML = '<span class="text-muted small">No dynamic parameters defined.</span>';
            }

            previewModal.show();
        });
    });
});
</script>
@endpush