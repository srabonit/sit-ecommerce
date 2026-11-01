@php
    $carbonCreated = !empty($log->created_at) ? \Carbon\Carbon::parse($log->created_at) : null;
    $respStatus = (int) ($log->response_status ?? 200);
@endphp
<div class="modal-body p-3 font-sans">
    <!-- Top Summary Card -->
    <div class="d-flex align-items-center justify-content-between p-3 rounded-2 border bg-light mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-secondary text-white px-2 py-0.5 font-monospace">{{ strtoupper($log->request_method ?? 'GET') }}</span>
                <span class="font-monospace fw-bold text-dark fs-6">{{ $log->url_path }}</span>
            </div>
            <small class="text-muted">
                Recorded on <strong>{{ $carbonCreated ? $carbonCreated->format('Y-m-d H:i:s') : 'N/A' }}</strong> 
                ({{ $carbonCreated ? $carbonCreated->diffForHumans() : '' }})
            </small>
        </div>
        <div class="text-end">
            <span class="badge {{ $respStatus >= 400 ? 'bg-danger text-white' : 'bg-success text-white' }} px-2.5 py-1 fs-6 font-monospace">
                HTTP {{ $respStatus }}
            </span>
        </div>
    </div>

    <!-- Forensic Breakdown Table -->
    <table class="table table-sm table-borderless mb-3" style="font-size: 12.5px;">
        <tbody>
            <tr>
                <td class="text-muted" style="width: 140px;">Client IP Address:</td>
                <td class="font-monospace fw-bold text-dark">
                    <a href="{{ route('admin.security.ips.show', $log->ip_address) }}" class="text-decoration-none text-dark hover-primary" target="_blank">
                        {{ $log->ip_address }} <i class="fas fa-external-link-alt text-secondary ms-1" style="font-size: 10px;"></i>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Geo Country:</td>
                <td class="text-dark"><i class="fas fa-globe text-secondary me-1"></i> {{ $log->country ?: 'Unknown' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Security Disposition:</td>
                <td>
                    <span class="badge {{ $log->security_status === 'blocked' ? 'bg-danger text-white' : 'bg-success text-white' }} px-2 py-0.5 text-uppercase" style="font-size: 10px;">
                        {{ $log->security_status }}
                    </span>
                    <span class="badge bg-light text-dark border ms-1 font-monospace" style="font-size: 10px;">
                        Risk Score: {{ $log->risk_score ?: 0 }}/100
                    </span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Activity Category:</td>
                <td class="text-dark fw-semibold text-uppercase font-monospace" style="font-size: 11px;">
                    {{ str_replace('_', ' ', $log->activity_type ?? 'traffic') }}
                </td>
            </tr>
            <tr>
                <td class="text-muted">Authenticated User:</td>
                <td>
                    @if($log->user)
                        <span class="fw-semibold text-dark">{{ $log->user->name }}</span> (ID: #{{ $log->user_id }})
                    @else
                        <span class="text-muted">Unauthenticated Guest</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted">Client Environment:</td>
                <td class="text-dark">
                    {{ $log->device_type ?? 'Desktop' }} | {{ $log->browser ?? 'Browser' }} | {{ $log->platform ?? 'OS' }}
                </td>
            </tr>
            <tr>
                <td class="text-muted">HTTP Referer:</td>
                <td class="text-break text-muted font-monospace small">
                    {{ $log->referer ?: 'Direct Access / None' }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- User Agent Full Header -->
    <div class="mb-3">
        <label class="form-label small fw-semibold text-secondary mb-1">Full User-Agent String</label>
        <div class="bg-light p-2 rounded-2 border font-monospace small text-muted text-break" style="font-size: 11px;">
            {{ $log->user_agent ?? 'N/A' }}
        </div>
    </div>

    <!-- Request Payload & Diagnostics -->
    @if(!empty($log->request_payload))
        <div class="mb-1">
            <label class="form-label small fw-semibold text-danger mb-1">Captured Payload / Diagnostic Data</label>
            <pre class="bg-dark text-warning p-2.5 rounded-2 font-monospace small mb-0 text-break" style="max-height: 200px; overflow-y: auto; font-size: 11px;">@if(is_array($log->request_payload)){{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}@else{{ $log->request_payload }}@endif</pre>
        </div>
    @endif
</div>
<div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
    <a href="{{ route('admin.security.ips.show', $log->ip_address) }}" class="btn btn-sm btn-outline-primary" target="_blank">
        <i class="fas fa-shield-halved me-1"></i> Inspect All IP Activity
    </a>
    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
</div>