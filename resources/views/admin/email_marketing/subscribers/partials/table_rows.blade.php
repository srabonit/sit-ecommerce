@forelse($subscribers as $sub)
    <tr id="subscriber-row-{{ $sub->id }}" class="align-middle">
        {{-- Bulk Selection Checkbox --}}
        <td class="text-center">
            <input type="checkbox" 
                   class="form-check-input subscriber-select-cb mt-0 shadow-none" 
                   value="{{ $sub->id }}" 
                   data-email="{{ $sub->email }}" 
                   style="cursor: pointer; width: 15px; height: 15px; border-radius: 4px;">
        </td>

        {{-- ID --}}
        <td class="text-muted font-monospace small">#{{ $sub->id }}</td>

        {{-- Subscriber & Details --}}
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold text-uppercase flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px; border-radius: 5px;">
                    {{ strtoupper(substr($sub->email, 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="fw-bold text-dark text-truncate" style="max-width: 230px;" title="{{ $sub->email }}">
                        {{ $sub->email }}
                    </div>
                    <div class="text-muted small" style="font-size: 11px;">
                        {{ $sub->full_name !== $sub->email ? $sub->full_name : 'No Name' }}
                        @if($sub->phone)
                            • <i class="fas fa-phone-alt ms-1 text-secondary" style="font-size: 9px;"></i> <span class="font-monospace">{{ $sub->phone }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </td>

        {{-- Account Type --}}
        <td>
            @if($sub->user_id && $sub->user)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                    <i class="fas fa-user-check me-1"></i> Registered User
                </span>
            @else
                <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                    <i class="fas fa-user-clock me-1"></i> Guest Subscriber
                </span>
            @endif
        </td>

        {{-- Consent & Subscription Status --}}
        <td>
            @php
                $statusBadge = match($sub->status) {
                    'subscribed'   => 'bg-success text-white',
                    'unsubscribed' => 'bg-warning text-dark',
                    'bounced'      => 'bg-danger text-white',
                    default        => 'bg-secondary text-white',
                };
            @endphp
            <span class="badge {{ $statusBadge }} px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                {{ ucfirst($sub->status) }}
            </span>
            @if($sub->marketing_consent)
                <span class="badge bg-success-subtle text-success ms-1 border border-success-subtle px-1.5 py-0.5" style="font-size: 10px; border-radius: 4px;" title="Marketing Opt-in Verified">
                    <i class="fas fa-check"></i> Consented
                </span>
            @endif
        </td>

        {{-- Emailed History Counter --}}
        <td>
            @php $emailedCount = (int)($sub->campaign_deliveries_count ?? 0); @endphp
            @if($emailedCount > 0)
                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                    <i class="fas fa-paper-plane me-1"></i> {{ $emailedCount }} {{ \Illuminate\Support\Str::plural('email', $emailedCount) }} sent
                </span>
            @else
                <span class="badge bg-light text-muted border font-monospace px-2 py-1" style="font-size: 10.5px; border-radius: 4px;">
                    <i class="fas fa-envelope-open me-1 opacity-50"></i> Never Emailed
                </span>
            @endif
        </td>

        {{-- Source & IP --}}
        <td>
            <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 11px; border-radius: 4px;">
                {{ ucfirst(str_replace('_', ' ', $sub->consent_source ?? 'Direct')) }}
            </span>
            <div class="text-muted font-monospace" style="font-size: 10px;">{{ $sub->consent_ip ?? '—' }}</div>
        </td>

        {{-- Joined Date --}}
        <td>
            <span class="text-dark fw-semibold" style="font-size: 12px;">{{ $sub->created_at ? $sub->created_at->format('M d, Y') : '—' }}</span>
            <div class="text-muted" style="font-size: 10.5px;">{{ $sub->created_at ? $sub->created_at->format('h:i A') : '' }}</div>
        </td>

        {{-- Row Actions --}}
        <td class="text-end">
            <div class="btn-group btn-group-sm shadow-xs" style="border-radius: 5px;">
                {{-- Quick Single Direct Email --}}
                <button type="button" 
                        class="btn btn-light border py-1 px-2" 
                        onclick="openDirectEmailModal({{ $sub->id }}, '{{ addslashes($sub->email) }}', '{{ addslashes($sub->full_name) }}')" 
                        title="Send Direct Email">
                    <i class="fas fa-paper-plane text-info"></i>
                </button>

                {{-- Toggle Status Ajax --}}
                <button type="button" 
                        class="btn btn-light border py-1 px-2" 
                        onclick="toggleSubscriberStatusAjax({{ $sub->id }})" 
                        title="Toggle Subscription Status">
                    @if($sub->status === 'subscribed')
                        <i class="fas fa-ban text-warning"></i>
                    @else
                        <i class="fas fa-check-circle text-success"></i>
                    @endif
                </button>

                {{-- Edit Modal Trigger --}}
                <button type="button" 
                        class="btn btn-light border py-1 px-2" 
                        onclick="openEditSubscriberModal({{ json_encode($sub) }})" 
                        title="Edit Profile">
                    <i class="fas fa-pencil text-primary"></i>
                </button>

                {{-- Delete Ajax --}}
                <button type="button" 
                        class="btn btn-light border py-1 px-2 text-danger" 
                        onclick="deleteSubscriberAjax({{ $sub->id }}, '{{ addslashes($sub->email) }}')" 
                        title="Delete Record">
                    <i class="fas fa-trash-can"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted">
            <i class="fas fa-users-slash fa-2x mb-2 d-block text-secondary opacity-50"></i>
            <h6 class="fw-bold mb-1 text-dark">No subscribers found</h6>
            <p class="small text-muted mb-0">No records match your selected filter criteria. Try adjusting the search parameters.</p>
        </td>
    </tr>
@endforelse