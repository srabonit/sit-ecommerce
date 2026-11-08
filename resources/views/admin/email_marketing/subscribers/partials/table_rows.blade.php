
@forelse($subscribers as $sub)
    @php
        $emailedCount = (int) ($sub->campaign_deliveries_count ?? 0);

        $statusConfig = match($sub->status) {
            'subscribed' => [
                'class' => 'subscriber-status-active',
                'icon' => 'fa-check-circle',
                'label' => 'Subscribed',
            ],
            'unsubscribed' => [
                'class' => 'subscriber-status-unsubscribed',
                'icon' => 'fa-pause-circle',
                'label' => 'Unsubscribed',
            ],
            'bounced' => [
                'class' => 'subscriber-status-bounced',
                'icon' => 'fa-exclamation-circle',
                'label' => 'Bounced',
            ],
            default => [
                'class' => 'subscriber-status-default',
                'icon' => 'fa-circle',
                'label' => ucfirst($sub->status ?? 'Unknown'),
            ],
        };

        $isRegistered = (bool) ($sub->user_id && $sub->user);
        $displayName = $sub->full_name !== $sub->email
            ? $sub->full_name
            : 'No Name';

        $initial = strtoupper(substr($sub->email ?? '?', 0, 1));
        $source = ucfirst(str_replace('_', ' ', $sub->consent_source ?? 'Direct'));
    @endphp

    <tr id="subscriber-row-{{ $sub->id }}" class="subscriber-table-row align-middle">

        {{-- Bulk Selection --}}
        <td class="text-center subscriber-checkbox-cell">
            <input type="checkbox"
                   class="form-check-input subscriber-select-cb mt-0 shadow-none"
                   value="{{ $sub->id }}"
                   data-email="{{ $sub->email }}"
                   aria-label="Select subscriber {{ $sub->email }}">
        </td>

        {{-- Subscriber ID --}}
        <td>
            <span class="subscriber-id">#{{ $sub->id }}</span>
        </td>

        {{-- Subscriber Information --}}
        <td>
            <div class="subscriber-profile">

                <div class="subscriber-avatar">
                    {{ $initial }}
                </div>

                <div class="subscriber-profile-info">

                    <div class="subscriber-email"
                         title="{{ $sub->email }}">
                        {{ $sub->email }}
                    </div>

                    <div class="subscriber-meta">

                        <span class="subscriber-name">
                            {{ $displayName }}
                        </span>

                        @if($sub->phone)
                            <span class="subscriber-meta-separator">•</span>

                            <span class="subscriber-phone">
                                <i class="fas fa-phone-alt"></i>
                                {{ $sub->phone }}
                            </span>
                        @endif

                    </div>

                </div>
            </div>
        </td>

        {{-- Account Type --}}
        <td>
            @if($isRegistered)
                <span class="subscriber-badge subscriber-account-registered">
                    <i class="fas fa-user-check"></i>
                    Registered User
                </span>
            @else
                <span class="subscriber-badge subscriber-account-guest">
                    <i class="fas fa-user-clock"></i>
                    Guest Subscriber
                </span>
            @endif
        </td>

        {{-- Subscription Status & Consent --}}
        <td>
            <div class="subscriber-status-wrapper">

                <span class="subscriber-badge {{ $statusConfig['class'] }}">
                    <i class="fas {{ $statusConfig['icon'] }}"></i>
                    {{ $statusConfig['label'] }}
                </span>

                @if($sub->marketing_consent)
                    <span class="subscriber-badge subscriber-consent-badge"
                          title="Marketing Opt-in Verified">
                        <i class="fas fa-check"></i>
                        Consented
                    </span>
                @endif

            </div>
        </td>

        {{-- Email History --}}
        <td>
            @if($emailedCount > 0)
                <span class="subscriber-badge subscriber-email-sent">
                    <i class="fas fa-paper-plane"></i>
                    {{ $emailedCount }}
                    {{ \Illuminate\Support\Str::plural('email', $emailedCount) }}
                    sent
                </span>
            @else
                <span class="subscriber-badge subscriber-email-never">
                    <i class="fas fa-envelope-open"></i>
                    Never Emailed
                </span>
            @endif
        </td>

        {{-- Source & IP --}}
        <td>
            <div class="subscriber-source-info">

                <span class="subscriber-source-badge">
                    {{ $source }}
                </span>

                <span class="subscriber-ip">
                    {{ $sub->consent_ip ?? '—' }}
                </span>

            </div>
        </td>

        {{-- Joined Date --}}
        <td>
            <div class="subscriber-date-info">

                <span class="subscriber-date">
                    {{ $sub->created_at ? $sub->created_at->format('M d, Y') : '—' }}
                </span>

                @if($sub->created_at)
                    <span class="subscriber-time">
                        <i class="far fa-clock"></i>
                        {{ $sub->created_at->format('h:i A') }}
                    </span>
                @endif

            </div>
        </td>

        {{-- Actions --}}
        <td class="text-end subscriber-actions-cell">

            <div class="subscriber-action-group">

                {{-- Direct Email --}}
                <button type="button"
                        class="subscriber-action-btn subscriber-action-email"
                        onclick="openDirectEmailModal(
                            {{ $sub->id }},
                            {{ Js::from($sub->email) }},
                            {{ Js::from($sub->full_name) }}
                        )"
                        title="Send Direct Email"
                        aria-label="Send direct email">

                    <i class="fas fa-paper-plane"></i>
                </button>

                {{-- Toggle Subscription Status --}}
                <button type="button"
                        class="subscriber-action-btn subscriber-action-toggle"
                        onclick="toggleSubscriberStatusAjax({{ $sub->id }})"
                        title="Toggle Subscription Status"
                        aria-label="Toggle subscription status">

                    @if($sub->status === 'subscribed')
                        <i class="fas fa-ban"></i>
                    @else
                        <i class="fas fa-check-circle"></i>
                    @endif

                </button>

                {{-- Edit Subscriber --}}
                <button type="button"
                        class="subscriber-action-btn subscriber-action-edit"
                        onclick="openEditSubscriberModal({{ Js::from($sub) }})"
                        title="Edit Subscriber"
                        aria-label="Edit subscriber">

                    <i class="fas fa-pencil-alt"></i>
                </button>

                {{-- Delete Subscriber --}}
                <button type="button"
                        class="subscriber-action-btn subscriber-action-delete"
                        onclick="deleteSubscriberAjax(
                            {{ $sub->id }},
                            {{ Js::from($sub->email) }}
                        )"
                        title="Delete Subscriber"
                        aria-label="Delete subscriber">

                    <i class="fas fa-trash-alt"></i>
                </button>

            </div>

        </td>

    </tr>

@empty

    {{-- Empty State --}}
    <tr>
        <td colspan="9" class="subscriber-empty-cell">

            <div class="subscriber-empty-state">

                <div class="subscriber-empty-icon">
                    <i class="fas fa-users-slash"></i>
                </div>

                <h6>No Subscribers Found</h6>

                <p>
                    No records match your selected filter criteria.
                    Try adjusting your search parameters.
                </p>

            </div>

        </td>
    </tr>

@endforelse


<style>
    /* ==========================================
       SUBSCRIBER TABLE ROWS - MODERN UI
       Strict 5px Border Radius
    ========================================== */

    #email-marketing-subscribers-root {
        --sub-primary: #4361ee;
        --sub-primary-soft: #eef2ff;
        --sub-success: #16a36a;
        --sub-success-soft: #eaf8f1;
        --sub-warning: #d18a13;
        --sub-warning-soft: #fff7e6;
        --sub-danger: #dc3545;
        --sub-danger-soft: #fff0f0;
        --sub-info: #1684c7;
        --sub-info-soft: #eaf6fd;
        --sub-text: #253047;
        --sub-muted: #8490a3;
        --sub-border: #e9edf3;
    }

    #email-marketing-subscribers-root .subscriber-table-row {
        transition: background-color .18s ease;
    }

    #email-marketing-subscribers-root .subscriber-table-row:hover {
        background: #f8faff;
    }

    #email-marketing-subscribers-root .subscriber-table-row td {
        padding: 13px 12px;
        border-bottom: 1px solid var(--sub-border);
        vertical-align: middle;
    }

    /* Checkbox */

    #email-marketing-subscribers-root .subscriber-checkbox-cell {
        width: 42px;
    }

    #email-marketing-subscribers-root .subscriber-select-cb {
        width: 16px;
        height: 16px;
        border-radius: 5px;
        border-color: #cbd3df;
        cursor: pointer;
        vertical-align: middle;
    }

    #email-marketing-subscribers-root .subscriber-select-cb:checked {
        background-color: var(--sub-primary);
        border-color: var(--sub-primary);
    }

    /* Subscriber ID */

    #email-marketing-subscribers-root .subscriber-id {
        display: inline-flex;
        align-items: center;
        font-size: 12px;
        font-weight: 600;
        color: #7c8799;
        font-family: monospace;
        white-space: nowrap;
    }

    /* Profile */

    #email-marketing-subscribers-root .subscriber-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 210px;
    }

    #email-marketing-subscribers-root .subscriber-avatar {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: linear-gradient(135deg, #e9edff, #dce5ff);
        color: #4059c9;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
    }

    #email-marketing-subscribers-root .subscriber-profile-info {
        min-width: 0;
    }

    #email-marketing-subscribers-root .subscriber-email {
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--sub-text);
        font-size: 12.5px;
        font-weight: 700;
    }

    #email-marketing-subscribers-root .subscriber-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 4px;
        font-size: 10.5px;
        color: var(--sub-muted);
    }

    #email-marketing-subscribers-root .subscriber-name {
        font-weight: 500;
    }

    #email-marketing-subscribers-root .subscriber-meta-separator {
        color: #c4cad4;
    }

    #email-marketing-subscribers-root .subscriber-phone {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-family: monospace;
    }

    /* Common Badges */

    #email-marketing-subscribers-root .subscriber-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 5px;
        font-size: 10.5px;
        line-height: 1.3;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    #email-marketing-subscribers-root .subscriber-badge i {
        font-size: 10px;
    }

    /* Account Type */

    #email-marketing-subscribers-root .subscriber-account-registered {
        background: #edf2ff;
        color: #4059c9;
        border-color: #dce5ff;
    }

    #email-marketing-subscribers-root .subscriber-account-guest {
        background: #f3f5f8;
        color: #687487;
        border-color: #e5e9ef;
    }

    /* Subscription Status */

    #email-marketing-subscribers-root .subscriber-status-wrapper {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    #email-marketing-subscribers-root .subscriber-status-active {
        background: var(--sub-success-soft);
        color: var(--sub-success);
        border-color: #d3f0df;
    }

    #email-marketing-subscribers-root .subscriber-status-unsubscribed {
        background: var(--sub-warning-soft);
        color: var(--sub-warning);
        border-color: #f5e7c6;
    }

    #email-marketing-subscribers-root .subscriber-status-bounced {
        background: var(--sub-danger-soft);
        color: var(--sub-danger);
        border-color: #f6dada;
    }

    #email-marketing-subscribers-root .subscriber-status-default {
        background: #f1f3f6;
        color: #687487;
        border-color: #e2e6ec;
    }

    #email-marketing-subscribers-root .subscriber-consent-badge {
        background: #effaf4;
        color: #168653;
        border-color: #d7f0e1;
        font-size: 9.5px;
    }

    /* Email History */

    #email-marketing-subscribers-root .subscriber-email-sent {
        background: var(--sub-info-soft);
        color: var(--sub-info);
        border-color: #d7ecf9;
    }

    #email-marketing-subscribers-root .subscriber-email-never {
        background: #f3f5f7;
        color: #8792a2;
        border-color: #e7eaf0;
        font-size: 10px;
    }

    /* Source & IP */

    #email-marketing-subscribers-root .subscriber-source-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    #email-marketing-subscribers-root .subscriber-source-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 7px;
        background: #f4f6fa;
        border: 1px solid #e7eaf0;
        border-radius: 5px;
        color: #536075;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    #email-marketing-subscribers-root .subscriber-ip {
        color: #929bad;
        font-family: monospace;
        font-size: 10px;
        white-space: nowrap;
    }

    /* Date */

    #email-marketing-subscribers-root .subscriber-date-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
        white-space: nowrap;
    }

    #email-marketing-subscribers-root .subscriber-date {
        color: #354157;
        font-size: 11.5px;
        font-weight: 700;
    }

    #email-marketing-subscribers-root .subscriber-time {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #929bad;
        font-size: 10px;
    }

    /* Action Buttons */

    #email-marketing-subscribers-root .subscriber-action-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    #email-marketing-subscribers-root .subscriber-action-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e9f0;
        border-radius: 5px;
        background: #fff;
        font-size: 11px;
        cursor: pointer;
        transition: all .18s ease;
    }

    #email-marketing-subscribers-root .subscriber-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(30, 45, 75, .08);
    }

    #email-marketing-subscribers-root .subscriber-action-email {
        color: #1684c7;
    }

    #email-marketing-subscribers-root .subscriber-action-email:hover {
        background: #eaf6fd;
        border-color: #cce8f8;
    }

    #email-marketing-subscribers-root .subscriber-action-toggle {
        color: #d18a13;
    }

    #email-marketing-subscribers-root .subscriber-action-toggle:hover {
        background: #fff7e6;
        border-color: #f3e2bc;
    }

    #email-marketing-subscribers-root .subscriber-action-edit {
        color: #4361ee;
    }

    #email-marketing-subscribers-root .subscriber-action-edit:hover {
        background: #eef2ff;
        border-color: #dce4ff;
    }

    #email-marketing-subscribers-root .subscriber-action-delete {
        color: #dc3545;
    }

    #email-marketing-subscribers-root .subscriber-action-delete:hover {
        background: #fff0f0;
        border-color: #f5d4d7;
    }

    /* Empty State */

    #email-marketing-subscribers-root .subscriber-empty-cell {
        padding: 0 !important;
    }

    #email-marketing-subscribers-root .subscriber-empty-state {
        padding: 55px 20px;
        text-align: center;
    }

    #email-marketing-subscribers-root .subscriber-empty-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f2f5fa;
        border: 1px solid #e8edf4;
        border-radius: 5px;
        color: #9ba7b8;
        font-size: 23px;
    }

    #email-marketing-subscribers-root .subscriber-empty-state h6 {
        margin: 0 0 6px;
        color: #354157;
        font-size: 14px;
        font-weight: 750;
    }

    #email-marketing-subscribers-root .subscriber-empty-state p {
        max-width: 390px;
        margin: 0 auto;
        color: #8b96a8;
        font-size: 12px;
        line-height: 1.6;
    }

    /* Responsive */

    @media (max-width: 767.98px) {
        #email-marketing-subscribers-root .subscriber-table-row td {
            padding: 11px 9px;
        }

        #email-marketing-subscribers-root .subscriber-profile {
            min-width: 190px;
        }

        #email-marketing-subscribers-root .subscriber-email {
            max-width: 190px;
        }

        #email-marketing-subscribers-root .subscriber-action-btn {
            width: 29px;
            height: 29px;
        }

        #email-marketing-subscribers-root .subscriber-empty-state {
            padding: 40px 15px;
        }
    }
</style>
