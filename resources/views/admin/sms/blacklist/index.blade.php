@extends('layouts.admin')

@section('title', 'SMS Blacklist & Prevention')

@section('admin_content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">SMS Blacklist</h4>
            <p class="text-muted small mb-0">Block fraud numbers, abusive client IPs, and spam keywords.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#createBlacklistModal">
            <i class="fas fa-shield-alt me-1"></i> Add Entry
        </button>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Blocked Numbers</span>
                    <h5 class="fw-bold text-danger mt-1 mb-0">{{ number_format($stats['total_phones'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Restricted mobiles</span>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Blocked IPs</span>
                    <h5 class="fw-bold text-dark mt-1 mb-0">{{ number_format($stats['total_ips'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Restricted OTP IPs</span>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted small d-block">Filter Keywords</span>
                    <h5 class="fw-bold text-warning-emphasis mt-1 mb-0">{{ number_format($stats['total_keywords'] ?? 0) }}</h5>
                    <span class="text-muted" style="font-size: 11px;">Restricted spam words</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.blacklist.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search blocked number, IP, keyword, or reason..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="phone" {{ request('type') === 'phone' ? 'selected' : '' }}>Phone Numbers</option>
                        <option value="ip" {{ request('type') === 'ip' ? 'selected' : '' }}>IP Addresses</option>
                        <option value="keyword" {{ request('type') === 'keyword' ? 'selected' : '' }}>Spam Keywords</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 text-end">
                    @if(request()->hasAny(['search', 'type']))
                        <a href="{{ route('admin.sms.blacklist.index') }}" class="btn btn-light btn-sm px-2 text-muted me-1">Clear</a>
                    @endif
                    <button type="submit" class="btn btn-secondary btn-sm px-3">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Blacklist Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                        <th class="ps-3 py-3">Rule Type</th>
                        <th>Target Value</th>
                        <th style="min-width: 200px;">Reason / Notes</th>
                        <th>Status</th>
                        <th>Blocked Date</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($blacklists as $item)
                        <tr>
                            {{-- Type Badge --}}
                            <td class="ps-3 py-3">
                                @php
                                    $typeBadges = [
                                        'phone' => 'bg-danger-subtle text-danger border-danger-subtle',
                                        'ip' => 'bg-dark-subtle text-dark border-dark-subtle',
                                        'keyword' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    ];
                                @endphp
                                <span class="badge border {{ $typeBadges[$item->type] ?? 'bg-light text-secondary border' }}">
                                    {{ ucfirst($item->type) }}
                                </span>
                            </td>

                            {{-- Target Value --}}
                            <td>
                                <code class="fw-semibold text-dark fs-6">{{ $item->value }}</code>
                            </td>

                            {{-- Reason --}}
                            <td>
                                <span class="text-muted">{{ $item->reason ?: 'No notes provided' }}</span>
                            </td>

                            {{-- Active Switch Toggle --}}
                            <td>
                                <div class="form-check form-switch m-0">
                                    <input 
                                        class="form-check-input blacklist-toggle" 
                                        type="checkbox" 
                                        role="switch"
                                        data-url="{{ route('admin.sms.blacklist.toggle_status', $item->id) }}"
                                        {{ $item->is_active ? 'checked' : '' }}
                                    >
                                </div>
                            </td>

                            {{-- Blocked Date --}}
                            <td>
                                <div class="text-dark">{{ $item->created_at->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $item->created_at->format('h:i A') }}</div>
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 text-end">
                                <form action="{{ route('admin.sms.blacklist.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this rule from blacklist?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm text-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-shield-alt fs-3 d-block mb-2 text-success"></i>
                                No blacklisted items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($blacklists->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Showing {{ $blacklists->firstItem() }} to {{ $blacklists->lastItem() }} of {{ $blacklists->total() }} entries
                    </span>
                    {{ $blacklists->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- Modal: Add Blacklist Entry --}}
<div class="modal fade" id="createBlacklistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold">Add Blacklist Rule</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sms.blacklist.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark">Rule Type <span class="text-danger">*</span></label>
                        <select name="type" id="modalRuleType" class="form-select form-select-sm" required>
                            <option value="phone">Phone Number</option>
                            <option value="ip">IP Address</option>
                            <option value="keyword">Spam Keyword</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark" id="modalValueLabel">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="value" id="modalValueInput" class="form-control form-control-sm" placeholder="e.g. 017XXXXXXXX or 88017XXXXXXXX" required>
                        <span class="form-text small" id="modalValueHelp">Auto-formats to 880 standard.</span>
                    </div>

                    <div>
                        <label class="form-label fw-medium small text-dark">Reason / Note</label>
                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="e.g. Repeated OTP abuse, fake order spam">
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Add Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('content')
    @yield('admin_content')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Modal Input Helper Text Dynamic Switcher
    const ruleType = document.getElementById('modalRuleType');
    const valueLabel = document.getElementById('modalValueLabel');
    const valueInput = document.getElementById('modalValueInput');
    const valueHelp = document.getElementById('modalValueHelp');

    if (ruleType) {
        ruleType.addEventListener('change', function () {
            if (this.value === 'phone') {
                valueLabel.innerHTML = 'Phone Number <span class="text-danger">*</span>';
                valueInput.placeholder = 'e.g. 017XXXXXXXX or 88017XXXXXXXX';
                valueHelp.textContent = 'Auto-formats to 880 standard.';
            } else if (this.value === 'ip') {
                valueLabel.innerHTML = 'IP Address <span class="text-danger">*</span>';
                valueInput.placeholder = 'e.g. 192.168.1.1 or 103.145.x.x';
                valueHelp.textContent = 'IPv4 or IPv6 address.';
            } else {
                valueLabel.innerHTML = 'Spam Keyword <span class="text-danger">*</span>';
                valueInput.placeholder = 'e.g. lottery, fake, free prize';
                valueHelp.textContent = 'Matching messages will be auto-flagged and blocked.';
            }
        });
    }

    // 2. Blacklist AJAX Status Toggle
    document.querySelectorAll('.blacklist-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const url = this.getAttribute('data-url');
            const isChecked = this.checked;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toggle.checked = Boolean(data.is_active);
                } else {
                    toggle.checked = !isChecked;
                    alert(data.message || 'Failed to update rule status.');
                }
            })
            .catch(() => {
                toggle.checked = !isChecked;
                alert('Connection error while updating rule status.');
            });
        });
    });
});
</script>
@endpush