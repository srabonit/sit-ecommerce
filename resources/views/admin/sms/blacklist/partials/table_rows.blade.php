
@forelse($blacklists as $item)
    @php
        $destroyUrl = Route::has('admin.sms.blacklist.destroy') ? route('admin.sms.blacklist.destroy', $item->id) : url('admin/sms/blacklist/' . $item->id);
    @endphp

    <tr>
        {{-- Phone Number --}}
        <td class="ps-3 ps-md-4 py-3">
            <div class="d-flex align-items-center gap-2">
                <span class="sms-blacklist-phone fw-semibold text-dark font-monospace">
                    {{ $item->value }}
                </span>
            </div>
        </td>

        {{-- Reason --}}
        <td>
            <span class="sms-blacklist-reason text-muted">
                {{ $item->reason ?: 'No notes provided' }}
            </span>
        </td>

        {{-- Blocked Date --}}
        <td>
            <div class="sms-blacklist-date text-dark fw-medium">
                {{ $item->created_at->format('M d, Y') }}
            </div>
            <div class="sms-blacklist-time text-muted">
                <i class="far fa-clock me-1"></i>
                {{ $item->created_at->format('h:i A') }}
            </div>
        </td>

        {{-- Action: Unblock Button --}}
        <td class="pe-3 pe-md-4 text-end">
            <button
                type="button"
                class="btn btn-outline-danger btn-sm px-3 py-2 btn-unblock d-inline-flex align-items-center justify-content-center"
                data-action="{{ $destroyUrl }}"
                data-phone="{{ $item->value }}"
            >
                <i class="fas fa-unlock me-2"></i>
                Unblock
            </button>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="4" class="text-center py-5">
            <div class="sms-blacklist-empty">
                <div class="sms-blacklist-empty-icon mb-3">
                    <i class="fas fa-shield-alt"></i>
                </div>

                <h6 class="fw-semibold text-dark mb-1">
                    No Blocked Numbers
                </h6>

                <p class="text-muted small mb-0">
                    No blocked phone numbers found.
                </p>
            </div>
        </td>
    </tr>
@endforelse

<style>
/* SMS Blacklist Table Rows */
.sms-blacklist-page .sms-blacklist-phone {
    display: inline-block;
    font-size: 13px;
    letter-spacing: .2px;
    overflow-wrap: anywhere;
}

.sms-blacklist-page .sms-blacklist-reason {
    display: inline-block;
    font-size: 12px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.sms-blacklist-page .sms-blacklist-date {
    font-size: 12px;
    line-height: 1.5;
}

.sms-blacklist-page .sms-blacklist-time {
    font-size: 11px;
    margin-top: 3px;
}

.sms-blacklist-page .btn-unblock {
    min-height: 32px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    transition: all .18s ease;
}

.sms-blacklist-page .btn-unblock:hover {
    box-shadow: 0 2px 6px rgba(220, 53, 69, .12);
}

.sms-blacklist-page .sms-blacklist-empty {
    padding: 12px 10px;
}

.sms-blacklist-page .sms-blacklist-empty-icon {
    width: 54px;
    height: 54px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #effaf3;
    color: #16a34a;
    font-size: 22px;
}

.sms-blacklist-page .sms-blacklist-empty h6 {
    font-size: 13px;
}

.sms-blacklist-page .sms-blacklist-empty p {
    font-size: 12px;
    line-height: 1.6;
}

@media (max-width: 575.98px) {
    .sms-blacklist-page .sms-blacklist-phone {
        font-size: 12px;
    }

    .sms-blacklist-page .sms-blacklist-reason {
        font-size: 11.5px;
    }

    .sms-blacklist-page .btn-unblock {
        min-height: 34px;
        padding-left: 10px !important;
        padding-right: 10px !important;
        font-size: 11px;
    }
}
</style>
