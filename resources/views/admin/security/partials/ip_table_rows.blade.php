@forelse($ips as $item)
    @php
        $riskScore = (int) ($item->risk_score ?? $item->max_risk_score ?? 0);

        $riskBadge = match(true) {
            $riskScore >= 75 => 'bg-danger text-white',
            $riskScore >= 40 => 'bg-warning text-dark',
            default          => 'bg-success text-white',
        };

        $isBlocked = (bool) ($item->is_blocked ?? false);

        $countryName = (!empty($item->country) && !in_array($item->country, ['Unknown', 'Localhost (BD)', 'N/A'], true))
            ? $item->country
            : 'Bangladesh';

        $totalHits = (int) ($item->total_requests ?? $item->total_hits ?? 0);
    @endphp

    <tr id="ip-row-{{ Str::slug($item->ip_address) }}"
        class="{{ $isBlocked ? 'table-danger bg-opacity-25' : '' }}">

        {{-- 1. IP Address & Version --}}
        <td class="ps-3 py-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border fw-semibold ip-version-badge">
                    {{ strtoupper($item->ip_version ?? 'v4') }}
                </span>

                <a href="{{ route('admin.security.ips.show', $item->ip_address) }}"
                   class="fw-bold text-dark text-decoration-none ip-address-link font-monospace">
                    {{ $item->ip_address }}
                </a>
            </div>

            @if($isBlocked)
                <div class="mt-2">
                    <span class="badge bg-danger text-white fw-semibold ip-status-badge">
                        <i class="fas fa-ban me-1"></i>
                        Blocked by Firewall
                    </span>
                </div>
            @endif
        </td>

        {{-- 2. Geo Country --}}
        <td class="py-3">
            <span class="badge bg-light text-dark border fw-medium ip-country-badge">
                <i class="fas fa-globe text-primary me-1"></i>
                {{ $countryName }}
            </span>
        </td>

        {{-- 3. Device, Browser & Platform --}}
        <td class="py-3">
            <div class="d-flex flex-column gap-1 ip-device-info">

                <span class="text-dark fw-semibold">
                    <i class="fas {{ (($item->device_type ?? 'Desktop') === 'Mobile' ? 'fa-mobile-screen' : (($item->device_type ?? 'Desktop') === 'Tablet' ? 'fa-tablet-screen' : 'fa-laptop')) }} me-1 text-secondary"></i>
                    {{ $item->device_type ?? 'Desktop' }}
                </span>

                <span class="text-muted ip-device-meta">
                    <strong class="text-secondary">{{ $item->browser ?? 'Chrome' }}</strong>
                    <span class="mx-1">/</span>
                    {{ $item->platform ?? 'Windows 10/11' }}
                </span>

            </div>
        </td>

        {{-- 4. Traffic & Total Hits --}}
        <td class="text-center py-3">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-semibold font-monospace ip-count-badge">
                {{ number_format($totalHits) }}
            </span>
        </td>

        {{-- 5. Orders Count --}}
        <td class="text-center py-3">
            @if(($item->total_orders ?? $item->order_count ?? 0) > 0)
                <a href="{{ route('admin.security.orders.clusters', ['search' => $item->ip_address]) }}"
                   class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none fw-semibold ip-count-badge"
                   title="View Orders from this IP">

                    <i class="fas fa-cart-shopping me-1"></i>
                    {{ $item->total_orders ?? $item->order_count }}
                </a>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        {{-- 6. Form Submissions Count --}}
        <td class="text-center py-3">
            @if(($item->total_forms ?? $item->form_count ?? 0) > 0)
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 fw-semibold ip-count-badge">
                    {{ $item->total_forms ?? $item->form_count }}
                </span>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        {{-- 7. Failed Login Attempts --}}
        <td class="text-center py-3">
            @if(($item->failed_logins ?? 0) > 0)
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fw-bold ip-count-badge">
                    <i class="fas fa-lock me-1"></i>
                    {{ $item->failed_logins }}
                </span>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        {{-- 8. Risk Score --}}
        <td class="text-center py-3">
            <span class="badge {{ $riskBadge }} fw-bold font-monospace ip-risk-badge">
                {{ $riskScore }}/100
            </span>
        </td>

        {{-- 9. Last Activity Timestamp --}}
        <td class="py-3">
            <div class="d-flex flex-column gap-1 ip-activity-info">

                <span class="text-dark fw-semibold">
                    {{ $item->last_activity ? \Carbon\Carbon::parse($item->last_activity)->diffForHumans() : 'N/A' }}
                </span>

                <span class="text-muted ip-activity-date">
                    {{ $item->last_activity ? \Carbon\Carbon::parse($item->last_activity)->format('M d, Y H:i') : '' }}
                </span>

            </div>
        </td>

        {{-- 10. Status & Actions --}}
        <td class="pe-3 py-3 text-end text-nowrap">
            <div class="btn-group btn-group-sm ip-action-group" role="group">

                {{-- View Details Profile --}}
                <a href="{{ route('admin.security.ips.show', $item->ip_address) }}"
                   class="btn btn-outline-secondary"
                   title="View Full Activity Timeline"
                   aria-label="View IP activity timeline">

                    <i class="fas fa-eye"></i>
                </a>

                {{-- 1-Click Direct Block / Unblock Trigger --}}
                @if($isBlocked)
                    <button type="button"
                            class="btn btn-outline-success btn-trigger-unblock btn-direct-unblock"
                            data-ip="{{ $item->ip_address }}"
                            title="Lift Firewall Block"
                            aria-label="Unblock IP address">

                        <i class="fas fa-lock-open"></i>
                    </button>
                @else
                    <button type="button"
                            class="btn btn-outline-danger btn-trigger-block btn-direct-block"
                            data-ip="{{ $item->ip_address }}"
                            title="Block this IP Address"
                            aria-label="Block IP address">

                        <i class="fas fa-ban"></i>
                    </button>
                @endif

            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="10" class="text-center py-5">

            <div class="d-flex flex-column align-items-center justify-content-center ip-empty-state">

                <div class="ip-empty-icon mb-3">
                    <i class="fas fa-shield-cat"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1">
                    No IP Address Records Found
                </h6>

                <p class="text-muted small mb-0">
                    Try adjusting your search queries or date range filters.
                </p>

            </div>

        </td>
    </tr>
@endforelse

<style>
    /* ==========================================
       IP SECURITY TABLE - KAIADMIN CLASSIC
       Scoped Styling | Strict 5px Radius
    ========================================== */

    .ip-version-badge,
    .ip-status-badge,
    .ip-country-badge,
    .ip-count-badge,
    .ip-risk-badge,
    .ip-action-group .btn,
    .ip-empty-icon {
        border-radius: 5px !important;
    }

    /* IP Address */
    .ip-version-badge {
        font-size: 10px;
        padding: 4px 6px;
        min-width: 30px;
        text-align: center;
    }

    .ip-address-link {
        font-size: 12.5px;
        letter-spacing: -.2px;
        transition: color .2s ease;
    }

    .ip-address-link:hover {
        color: var(--bs-primary, #1572e8) !important;
    }

    .ip-status-badge {
        font-size: 10px;
        padding: 4px 7px;
    }

    /* Country */
    .ip-country-badge {
        font-size: 11px;
        padding: 6px 9px;
        font-weight: 500;
    }

    /* Device Information */
    .ip-device-info {
        font-size: 11.5px;
        line-height: 1.5;
        min-width: 125px;
    }

    .ip-device-meta {
        font-size: 10.5px;
        line-height: 1.4;
        white-space: nowrap;
    }

    /* Metrics */
    .ip-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        padding: 5px 9px;
        font-size: 11px;
        white-space: nowrap;
    }

    .ip-risk-badge {
        display: inline-block;
        min-width: 52px;
        padding: 5px 8px;
        font-size: 10.5px;
        white-space: nowrap;
    }

    /* Last Activity */
    .ip-activity-info {
        min-width: 115px;
        line-height: 1.4;
    }

    .ip-activity-info > span:first-child {
        font-size: 11.5px;
    }

    .ip-activity-date {
        font-size: 10px;
        white-space: nowrap;
    }

    /* Action Buttons */
    .ip-action-group {
        box-shadow: none !important;
        gap: 5px;
    }

    .ip-action-group .btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        transition: all .2s ease;
    }

    .ip-action-group .btn:hover {
        transform: translateY(-1px);
    }

    /* Blocked IP Row */
    tr.table-danger.bg-opacity-25 td {
        background-color: rgba(220, 53, 69, .035) !important;
    }

    /* Empty State */
    .ip-empty-state {
        min-height: 155px;
    }

    .ip-empty-icon {
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(108, 117, 125, .08);
        color: #89919a;
        font-size: 25px;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        .ip-action-group .btn {
            width: 32px;
            height: 32px;
        }

        .ip-device-info {
            min-width: 115px;
        }

        .ip-activity-info {
            min-width: 105px;
        }
    }
</style>