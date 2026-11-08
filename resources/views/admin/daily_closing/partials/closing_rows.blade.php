
@forelse($closings as $c)
    @php
        $status = strtolower($c->status ?? 'closed');
        $diff = (float) ($c->difference ?? 0.00);
    @endphp

    <tr class="align-middle">

        <!-- ID -->
        <td class="py-2 font-monospace bn-digit text-muted text-nowrap"
            style="font-size: 11px;">
            #{{ $c->id }}
        </td>

        <!-- Closing Date -->
        <td class="py-2 font-monospace text-dark"
            style="font-size: 11px; min-width: 100px;">
            <span class="fw-semibold">
                {{ date('d M Y', strtotime($c->closing_date)) }}
            </span>

            @if(!empty($c->closed_at))
                <small class="text-muted d-block mt-1"
                       style="font-size: 10px;">
                    {{ date('h:i A', strtotime($c->closed_at)) }}
                </small>
            @endif
        </td>

        <!-- Branch Name & Code -->
        <td class="py-2" style="min-width: 120px;">
            <div class="fw-semibold text-dark text-break"
                 style="max-width: 160px; font-size: 12px; line-height: 1.4;">
                {{ $c->branch_name }}
            </div>

            <small class="text-muted font-monospace bn-digit d-block mt-1"
                   style="font-size: 10px;">
                {{ $c->branch_code }}
            </small>
        </td>

        <!-- Cashier -->
        <td class="py-2" style="min-width: 130px;">
            <div class="fw-semibold text-dark text-break"
                 style="font-size: 12px; line-height: 1.4;">
                {{ $c->cashier_name ?? 'Counter Staff' }}
            </div>

            @if(!empty($c->verified_name))
                <small class="text-success d-block mt-1"
                       style="font-size: 10px; line-height: 1.5;">
                    <i class="fas fa-check-double me-1"></i>
                    Verified by {{ $c->verified_name }}
                </small>
            @endif
        </td>

        <!-- Opening Cash -->
        <td class="py-2 text-end font-monospace bn-digit text-muted text-nowrap"
            style="font-size: 11px;">
            ৳{{ number_format($c->opening_cash, 2) }}
        </td>

        <!-- POS Cash Sales -->
        <td class="py-2 text-end font-monospace bn-digit text-success text-nowrap"
            style="font-size: 11px;">
            <span class="fw-semibold">
                +৳{{ number_format($c->pos_cash_sales, 2) }}
            </span>

            <small class="text-muted d-block mt-1"
                   style="font-size: 10px;">
                ({{ $c->total_orders_count }} orders)
            </small>
        </td>

        <!-- Digital Sales -->
        <td class="py-2 text-end font-monospace bn-digit text-primary text-nowrap"
            style="font-size: 11px;">
            ৳{{ number_format($c->digital_sales_amount, 2) }}
        </td>

        <!-- Expected Cash -->
        <td class="py-2 text-end font-monospace bn-digit text-dark fw-bold text-nowrap"
            style="font-size: 11px;">
            ৳{{ number_format($c->expected_cash, 2) }}
        </td>

        <!-- Actual Counted Cash -->
        <td class="py-2 text-end font-monospace bn-digit fw-bold text-nowrap {{ $c->actual_cash !== null ? 'text-dark' : 'text-muted' }}"
            style="font-size: 11px;">
            {{ $c->actual_cash !== null ? '৳' . number_format($c->actual_cash, 2) : 'Pending' }}
        </td>

        <!-- Difference / Discrepancy -->
        <td class="py-2 text-end font-monospace bn-digit fw-bold text-nowrap {{ $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : 'text-primary') }}"
            style="font-size: 11px;">
            @if($c->status === 'open')
                <span class="text-muted fw-normal"
                      style="font-size: 10px;">
                    In Progress
                </span>
            @else
                {{ $diff >= 0 ? '+' : '' }}৳{{ number_format($diff, 2) }}
            @endif
        </td>

        <!-- Session Status -->
        <td class="py-2 text-center"
            style="min-width: 90px;">
            @if($status === 'verified')
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-check-circle me-1"></i>
                    Verified
                </span>
            @elseif($status === 'closed')
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    Closed
                </span>
            @else
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-clock me-1"></i>
                    Open
                </span>
            @endif
        </td>

        <!-- Actions -->
        <td class="py-2 text-end text-nowrap"
            style="min-width: 112px;">
            <div class="btn-group btn-group-sm"
                 role="group">

                <!-- Quick Modal Breakdown -->
                <button type="button"
                        class="btn btn-light border text-secondary px-2 py-1"
                        title="View Breakdown"
                        onclick="showClosingDetailsModal({{ $c->id }})"
                        style="min-width: 32px; min-height: 32px; font-size: 11px;">
                    <i class="fas fa-eye"></i>
                </button>

                <!-- Thermal Print Summary Slip -->
                <a href="{{ route('admin.daily_closing.print', $c->id) }}"
                   target="_blank"
                   class="btn btn-light border text-dark px-2 py-1"
                   title="Print Summary"
                   style="min-width: 32px; min-height: 32px; font-size: 11px;">
                    <i class="fas fa-print"></i>
                </a>

                <!-- Verify Button for Managers (Only if Closed & Unverified) -->
                @if($status === 'closed' && (Auth::user()->isSuperAdmin() || Auth::user()->role === 'admin' || Auth::user()->role === 'branch_manager'))
                    <button type="button"
                            class="btn btn-light border text-success px-2 py-1"
                            title="Verify &amp; Lock"
                            onclick="verifySessionRow({{ $c->id }})"
                            style="min-width: 32px; min-height: 32px; font-size: 11px;">
                        <i class="fas fa-shield-alt"></i>
                    </button>
                @endif

            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="12" class="text-center py-4 text-muted">
            <i class="fas fa-cash-register fa-2x d-block mb-2 text-secondary opacity-50"></i>

            <span class="fw-semibold d-block text-dark"
                  style="font-size: 12px;">
                No Records Found
            </span>

            <small class="text-muted"
                   style="font-size: 11px;">
                No sessions match your filters.
            </small>
        </td>
    </tr>
@endforelse
