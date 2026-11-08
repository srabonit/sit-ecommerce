<style>
/* =========================================================
   SECURITY LOG DETAILS
   Kaiadmin Lite v1.2.0 - Classic White Design
========================================================= */

#modalInspectLogDetails {
    --sld-primary: var(--bs-primary, #1572e8);
    --sld-border: #e5e7eb;
    --sld-heading: #212529;
    --sld-text: #495057;
    --sld-muted: #8d9498;
    --sld-surface: #f8f9fa;
    --sld-radius: 5px;
}

#modalInspectLogDetails .modal-body {
    padding: 18px !important;
    color: var(--sld-text);
    font-size: 12px;
}

#modalInspectLogDetails .sld-summary {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    padding: 15px !important;
    background: var(--sld-surface) !important;
    border: 1px solid var(--sld-border) !important;
    border-radius: var(--sld-radius) !important;
}

#modalInspectLogDetails .sld-request-info {
    min-width: 0;
    flex: 1;
}

#modalInspectLogDetails .sld-request-line {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 7px;
}

#modalInspectLogDetails .sld-request-method {
    padding: 5px 8px !important;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
}

#modalInspectLogDetails .sld-request-url {
    color: var(--sld-heading);
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
    word-break: break-word;
}

#modalInspectLogDetails .sld-recorded-date {
    color: var(--sld-muted);
    font-size: 11px;
    line-height: 1.6;
}

#modalInspectLogDetails .sld-http-status {
    flex-shrink: 0;
    padding: 7px 10px !important;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

/* Section headings */

#modalInspectLogDetails .sld-section-title {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 10px;
    color: #495057;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .45px;
    text-transform: uppercase;
}

/* Forensic information table */

#modalInspectLogDetails .sld-details-table {
    width: 100%;
    margin-bottom: 18px;
    border: 1px solid var(--sld-border);
    border-radius: var(--sld-radius);
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    font-size: 12px !important;
}

#modalInspectLogDetails .sld-details-table tbody tr {
    transition: background-color .15s ease;
}

#modalInspectLogDetails .sld-details-table tbody tr:nth-child(even) {
    background: #fafbfc;
}

#modalInspectLogDetails .sld-details-table tbody tr:hover {
    background: #f4f7fb;
}

#modalInspectLogDetails .sld-details-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #edf0f2;
    vertical-align: middle;
    line-height: 1.5;
}

#modalInspectLogDetails .sld-details-table tbody tr:last-child td {
    border-bottom: 0;
}

#modalInspectLogDetails .sld-details-table td:first-child {
    width: 155px;
    min-width: 125px;
    color: var(--sld-muted);
    font-size: 11px;
    font-weight: 600;
}

#modalInspectLogDetails .sld-details-table td:last-child {
    color: var(--sld-heading);
    overflow-wrap: anywhere;
    word-break: break-word;
}

#modalInspectLogDetails .sld-details-table .font-monospace {
    font-size: 11px;
}

#modalInspectLogDetails .sld-details-table .badge {
    padding: 5px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
}

/* IP link */

#modalInspectLogDetails .sld-ip-link {
    color: var(--sld-heading);
    font-weight: 700;
    text-decoration: none;
    overflow-wrap: anywhere;
}

#modalInspectLogDetails .sld-ip-link:hover {
    color: var(--sld-primary) !important;
}

/* Diagnostic panels */

#modalInspectLogDetails .sld-diagnostic-panel {
    margin-bottom: 16px;
}

#modalInspectLogDetails .sld-diagnostic-content {
    padding: 12px !important;
    background: var(--sld-surface) !important;
    border: 1px solid var(--sld-border) !important;
    border-radius: var(--sld-radius) !important;
    color: #59636e !important;
    font-size: 11px;
    line-height: 1.65;
    overflow-wrap: anywhere;
    word-break: break-word;
    white-space: pre-wrap;
}

/* Payload */

#modalInspectLogDetails .sld-payload-content {
    max-height: 260px;
    overflow: auto;
    padding: 13px !important;
    background: #252b35 !important;
    border: 1px solid #343c48 !important;
    border-radius: var(--sld-radius) !important;
    color: #f5c76b !important;
    font-size: 11px !important;
    line-height: 1.65;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
    word-break: break-word;
    scrollbar-width: thin;
    scrollbar-color: #687383 #252b35;
}

#modalInspectLogDetails .sld-payload-content::-webkit-scrollbar {
    width: 7px;
    height: 7px;
}

#modalInspectLogDetails .sld-payload-content::-webkit-scrollbar-thumb {
    background: #687383;
    border-radius: 10px;
}

/* Footer */

#modalInspectLogDetails .modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px 18px !important;
    background: #fff !important;
    border-top: 1px solid var(--sld-border) !important;
}

#modalInspectLogDetails .modal-footer .btn {
    min-height: 34px;
    padding: 7px 12px;
    border-radius: var(--sld-radius) !important;
    font-size: 11.5px;
    font-weight: 600;
}

/* Tablet */

@media (max-width: 575.98px) {
    #modalInspectLogDetails .modal-body {
        padding: 12px !important;
    }

    #modalInspectLogDetails .sld-summary {
        padding: 11px !important;
        gap: 8px;
    }

    #modalInspectLogDetails .sld-request-url {
        font-size: 12px;
    }

    #modalInspectLogDetails .sld-http-status {
        font-size: 10px;
        padding: 6px 7px !important;
    }

    #modalInspectLogDetails .sld-details-table td {
        padding: 9px 8px;
    }

    #modalInspectLogDetails .sld-details-table td:first-child {
        width: 115px;
        min-width: 105px;
        font-size: 10px;
    }

    #modalInspectLogDetails .sld-details-table td:last-child {
        font-size: 11px;
    }

    #modalInspectLogDetails .sld-diagnostic-content {
        padding: 10px !important;
    }

    #modalInspectLogDetails .modal-footer {
        padding: 10px 12px !important;
    }
}

/* Small mobile */

@media (max-width: 380px) {
    #modalInspectLogDetails .sld-summary {
        flex-direction: column;
    }

    #modalInspectLogDetails .sld-http-status {
        align-self: flex-start;
    }

    #modalInspectLogDetails .sld-details-table td:first-child {
        width: 100px;
        min-width: 95px;
    }

    #modalInspectLogDetails .modal-footer {
        align-items: stretch;
        flex-direction: column;
    }

    #modalInspectLogDetails .modal-footer .btn {
        width: 100%;
    }
}
</style>

@php
    $carbonCreated = !empty($log->created_at) ? \Carbon\Carbon::parse($log->created_at) : null;
    $respStatus = (int) ($log->response_status ?? 200);
@endphp

<div class="modal-body p-3 font-sans">

    <!-- Request Summary -->
    <div class="sld-summary d-flex align-items-center justify-content-between p-3 rounded-2 border bg-light mb-3">

        <div class="sld-request-info">
            <div class="sld-request-line d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-secondary text-white px-2 py-0.5 font-monospace sld-request-method">
                    {{ strtoupper($log->request_method ?? 'GET') }}
                </span>

                <span class="font-monospace fw-bold text-dark fs-6 sld-request-url">
                    {{ $log->url_path }}
                </span>
            </div>

            <small class="text-muted sld-recorded-date">
                Recorded on
                <strong>{{ $carbonCreated ? $carbonCreated->format('Y-m-d H:i:s') : 'N/A' }}</strong>
                ({{ $carbonCreated ? $carbonCreated->diffForHumans() : '' }})
            </small>
        </div>

        <div class="text-end">
            <span class="badge {{ $respStatus >= 400 ? 'bg-danger text-white' : 'bg-success text-white' }} px-2.5 py-1 fs-6 font-monospace sld-http-status">
                HTTP {{ $respStatus }}
            </span>
        </div>

    </div>

    <!-- Forensic Details -->
    <div class="sld-section-title">
        <i class="fas fa-fingerprint text-primary"></i>
        Request & Security Information
    </div>

    <table class="table table-sm table-borderless mb-3 sld-details-table">
        <tbody>

            <tr>
                <td class="text-muted">Client IP Address:</td>
                <td class="font-monospace fw-bold text-dark">
                    <a href="{{ route('admin.security.ips.show', $log->ip_address) }}" class="text-decoration-none text-dark hover-primary sld-ip-link" target="_blank">
                        {{ $log->ip_address }}
                        <i class="fas fa-external-link-alt text-secondary ms-1" style="font-size: 10px;"></i>
                    </a>
                </td>
            </tr>

            <tr>
                <td class="text-muted">Geo Country:</td>
                <td class="text-dark">
                    <i class="fas fa-globe text-secondary me-1"></i>
                    {{ $log->country ?: 'Unknown' }}
                </td>
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
                        <span class="fw-semibold text-dark">{{ $log->user->name }}</span>
                        (ID: #{{ $log->user_id }})
                    @else
                        <span class="text-muted">Unauthenticated Guest</span>
                    @endif
                </td>
            </tr>

            <tr>
                <td class="text-muted">Client Environment:</td>
                <td class="text-dark">
                    {{ $log->device_type ?? 'Desktop' }} |
                    {{ $log->browser ?? 'Browser' }} |
                    {{ $log->platform ?? 'OS' }}
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

    <!-- User Agent -->
    <div class="mb-3 sld-diagnostic-panel">

        <label class="form-label small fw-semibold text-secondary mb-2 sld-section-title">
            <i class="fas fa-desktop text-primary"></i>
            Full User-Agent String
        </label>

        <div class="bg-light p-2 rounded-2 border font-monospace small text-muted text-break sld-diagnostic-content">
            {{ $log->user_agent ?? 'N/A' }}
        </div>

    </div>

    <!-- Request Payload & Diagnostics -->
    @if(!empty($log->request_payload))
        <div class="mb-1 sld-diagnostic-panel">

            <label class="form-label small fw-semibold text-danger mb-2 sld-section-title">
                <i class="fas fa-code text-danger"></i>
                Captured Payload / Diagnostic Data
            </label>

            <pre class="bg-dark text-warning p-2.5 rounded-2 font-monospace small mb-0 text-break sld-payload-content">@if(is_array($log->request_payload)){{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}@else{{ $log->request_payload }}@endif</pre>

        </div>
    @endif

</div>

<!-- Modal Footer -->
<div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">

    <a href="{{ route('admin.security.ips.show', $log->ip_address) }}" class="btn btn-sm btn-outline-primary" target="_blank">
        <i class="fas fa-shield-halved me-1"></i>
        Inspect All IP Activity
    </a>

    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
        Close
    </button>

</div>