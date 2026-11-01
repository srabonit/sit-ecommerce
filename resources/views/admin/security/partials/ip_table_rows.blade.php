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
    <tr id="ip-row-{{ Str::slug($item->ip_address) }}" class="{{ $isBlocked ? 'table-danger bg-opacity-25' : '' }}">
        <!-- ১. IP Address & Version -->
        <td class="ps-3 font-monospace">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5" style="font-size: 10px;">
                    {{ strtoupper($item->ip_version ?? 'v4') }}
                </span>
                <a href="{{ route('admin.security.ips.show', $item->ip_address) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                    {{ $item->ip_address }}
                </a>
            </div>
            @if($isBlocked)
                <span class="badge bg-danger text-white px-1.5 py-0.5 mt-1" style="font-size: 9.5px;">
                    <i class="fas fa-ban me-1"></i> Blocked by Firewall
                </span>
            @endif
        </td>

        <!-- ২. Geo Country -->
        <td>
            <span class="badge bg-light text-dark border px-2 py-1">
                <i class="fas fa-globe text-primary me-1"></i> {{ $countryName }}
            </span>
        </td>

        <!-- ৩. Device, Browser & Platform -->
        <td>
            <div class="d-flex flex-column text-muted" style="font-size: 11.5px;">
                <span>
                    <i class="fas {{ (($item->device_type ?? 'Desktop') === 'Mobile' ? 'fa-mobile-screen' : (($item->device_type ?? 'Desktop') === 'Tablet' ? 'fa-tablet-screen' : 'fa-laptop')) }} me-1 text-secondary"></i>
                    {{ $item->device_type ?? 'Desktop' }}
                </span>
                <span class="text-secondary" style="font-size: 10.5px;">
                    <strong class="text-dark">{{ $item->browser ?? 'Chrome' }}</strong> / {{ $item->platform ?? 'Windows 10/11' }}
                </span>
            </div>
        </td>

        <!-- ৪. Traffic & Total Hits -->
        <td class="text-center">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 font-monospace">
                {{ number_format($totalHits) }}
            </span>
        </td>

        <!-- ৫. Orders Count -->
        <td class="text-center">
            @if(($item->total_orders ?? $item->order_count ?? 0) > 0)
                <a href="{{ route('admin.security.orders.clusters', ['search' => $item->ip_address]) }}" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none px-2 py-1" title="View Orders from this IP">
                    <i class="fas fa-cart-shopping me-1"></i> {{ $item->total_orders ?? $item->order_count }}
                </a>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        <!-- ৬. Form Submissions Count -->
        <td class="text-center">
            @if(($item->total_forms ?? $item->form_count ?? 0) > 0)
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                    {{ $item->total_forms ?? $item->form_count }}
                </span>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        <!-- ৭. Failed Login Attempts -->
        <td class="text-center">
            @if(($item->failed_logins ?? 0) > 0)
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 fw-bold">
                    <i class="fas fa-lock me-1"></i> {{ $item->failed_logins }}
                </span>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        <!-- ৮. Risk Score -->
        <td class="text-center">
            <span class="badge {{ $riskBadge }} px-2 py-1 font-monospace" style="font-size: 11px;">
                {{ $riskScore }}/100
            </span>
        </td>

        <!-- ৯. Last Activity Timestamp -->
        <td>
            <div class="d-flex flex-column" style="font-size: 11.5px;">
                <span class="text-dark fw-semibold">
                    {{ $item->last_activity ? \Carbon\Carbon::parse($item->last_activity)->diffForHumans() : 'N/A' }}
                </span>
                <span class="text-muted" style="font-size: 10px;">
                    {{ $item->last_activity ? \Carbon\Carbon::parse($item->last_activity)->format('M d, Y H:i') : '' }}
                </span>
            </div>
        </td>

        <!-- ১০. Status & Actions (Direct Block & View) -->
        <td class="pe-3 text-end text-nowrap">
            <div class="btn-group btn-group-sm shadow-sm">
                <!-- View Details Profile -->
                <a href="{{ route('admin.security.ips.show', $item->ip_address) }}" class="btn btn-outline-secondary" title="View Full Activity Timeline">
                    <i class="fas fa-eye"></i>
                </a>

                <!-- 1-Click Direct Block / Unblock Trigger -->
                @if($isBlocked)
                    <button type="button" 
                            class="btn btn-outline-success btn-trigger-unblock btn-direct-unblock" 
                            data-ip="{{ $item->ip_address }}" 
                            title="Lift Firewall Block">
                        <i class="fas fa-lock-open"></i>
                    </button>
                @else
                    <button type="button" 
                            class="btn btn-outline-danger btn-trigger-block btn-direct-block" 
                            data-ip="{{ $item->ip_address }}" 
                            title="Block this IP Address">
                        <i class="fas fa-ban"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center py-5 text-muted">
            <div class="d-flex flex-column align-items-center">
                <i class="fas fa-shield-cat fa-3x text-secondary mb-2 opacity-50"></i>
                <h6 class="fw-semibold mb-1">No IP Address Records Found</h6>
                <p class="small text-muted mb-0">Try adjusting your search queries or date range filters.</p>
            </div>
        </td>
    </tr>
@endforelse