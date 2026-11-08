
@forelse($transactions as $index => $txn)
    @php
        $status    = strtolower($txn->verification_status ?? 'pending_verification');
        $source    = strtolower($txn->source_type ?? 'checkout');
        $isTrashed = !empty($txn->deleted_at);

        $statusBadgeClass = match($status) {
            'pending_verification' => 'badge-soft-warning',
            'checked'              => 'badge-soft-info',
            'approved'             => 'badge-soft-primary',
            'paid'                 => 'badge-soft-success',
            'partially_paid'       => 'badge-soft-info',
            'unmatched'            => 'badge-soft-danger',
            'fake'                 => 'badge-soft-danger',
            'under_review'         => 'badge-soft-warning',
            'failed', 'cancelled'  => 'badge-soft-secondary',
            'refunded'             => 'badge-soft-secondary',
            default                => 'badge-soft-secondary',
        };

        $statusLabel = match($status) {
            'pending_verification' => 'Pending Verification',
            'checked'              => 'Admin Checked (Unpaid)',
            'approved'             => 'Approved',
            'paid'                 => 'Paid / Received',
            'partially_paid'       => 'Partially Paid',
            'unmatched'            => 'Unmatched',
            'fake'                 => 'Fake / Rejected',
            'under_review'         => 'Under Review',
            'failed'               => 'Failed',
            'cancelled'            => 'Cancelled',
            'refunded'             => 'Refunded',
            default                => ucwords(str_replace('_', ' ', $status)),
        };

        $sourceBadge = match($source) {
            'checkout' => ['label' => 'Checkout', 'class' => 'bg-primary-subtle text-primary border-primary-subtle', 'icon' => 'fa-globe'],
            'pos_cash' => ['label' => 'POS Cash', 'class' => 'bg-success-subtle text-success border-success-subtle', 'icon' => 'fa-cash-register'],
            'cod'      => ['label' => 'COD Order', 'class' => 'bg-warning-subtle text-dark border-warning-subtle', 'icon' => 'fa-truck'],
            'manual'   => ['label' => 'Manual', 'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle', 'icon' => 'fa-hand-holding-dollar'],
            'gateway'  => ['label' => 'Online PGW', 'class' => 'bg-info-subtle text-info border-info-subtle', 'icon' => 'fa-credit-card'],
            default    => ['label' => strtoupper($source), 'class' => 'bg-light text-dark border', 'icon' => 'fa-circle-dot'],
        };

        // Resolve payment proof screenshot supporting Global Media Library paths
        $proofUrl = null;
        if (!empty($txn->screenshot_path) && $txn->screenshot_path !== 'undefined' && $txn->screenshot_path !== 'null') {
            $rawProof = $txn->screenshot_path;
            $cleanPath = ltrim(str_replace(['public_html/', 'public/'], '', $rawProof), '/');
            if (class_exists(\App\Services\ImagePathService::class)) {
                $proofUrl = \App\Services\ImagePathService::resolveUrl($rawProof, '');
            } elseif (function_exists('show_image')) {
                $proofUrl = show_image($rawProof, '');
            } elseif (filter_var($rawProof, FILTER_VALIDATE_URL)) {
                $proofUrl = $rawProof;
            } elseif (file_exists(public_path($cleanPath))) {
                $proofUrl = asset($cleanPath);
            } elseif (file_exists(public_path('uploads/' . $cleanPath))) {
                $proofUrl = asset('uploads/' . $cleanPath);
            } elseif (file_exists(public_path('storage/' . $cleanPath))) {
                $proofUrl = asset('storage/' . $cleanPath);
            } else {
                $proofUrl = asset($cleanPath);
            }
        }

        $expectedAmt  = (float) ($txn->expected_amount ?? 0);
        $submittedAmt = (float) ($txn->submitted_amount ?? 0);
        $verifiedAmt  = (float) ($txn->verified_amount ?? 0);
        $dueAmt       = max(0, $expectedAmt - $verifiedAmt);

        $isUncheckedRow = empty($txn->checked_by) && empty($txn->verified_by);
    @endphp

    <tr id="txn-row-{{ $txn->id }}"
        class="align-middle border-bottom {{ !empty($txn->is_duplicate_flagged) ? 'table-danger bg-opacity-10' : ($isTrashed ? 'table-secondary bg-opacity-25' : '') }}">

        {{-- 1. Bulk Select Checkbox --}}
        <td class="ps-3 py-3 text-center" style="width: 42px;">
            <input type="checkbox"
                   class="form-check-input txn-row-checkbox shadow-none m-0"
                   value="{{ $txn->id }}"
                   onchange="handleRowCheckboxChange()"
                   style="cursor:pointer;width:15px;height:15px;border-radius:4px !important;">
        </td>

        {{-- 2. Serial Number --}}
        <td class="font-monospace text-muted fw-semibold bn-digit py-3 text-center"
            style="font-size:11px;width:48px;">
            {{ ($transactions->firstItem() ?? 1) + $index }}
        </td>

        {{-- 3. Transaction UID, Order & Source --}}
        <td class="py-3" style="width:165px;min-width:155px;">
            <a href="javascript:void(0)"
               onclick="openTransactionDetailModal({{ $txn->id }})"
               class="fw-bold text-primary text-decoration-none font-monospace bn-digit d-block mb-2 text-truncate"
               style="font-size:12px;"
               title="{{ $txn->transaction_uid }}">
                {{ $txn->transaction_uid }}
            </a>

            @if(!empty($txn->order_id))
                <div class="d-flex align-items-center gap-1 mb-2">
                    <span class="text-muted" style="font-size:10px;">Order:</span>
                    <a href="{{ route('admin.order.view', $txn->order_id) }}"
                       target="_blank"
                       class="font-monospace fw-bold text-dark text-decoration-underline bn-digit text-truncate"
                       style="font-size:11px;"
                       title="#{{ $txn->invoice_no ?: $txn->order_id }}">
                        #{{ $txn->invoice_no ?: $txn->order_id }}
                    </a>
                </div>
            @else
                <small class="text-muted d-block mb-2 font-monospace" style="font-size:10px;">
                    Unlinked
                </small>
            @endif

            <div class="d-flex align-items-center gap-1 flex-wrap">
                <span class="badge border {{ $sourceBadge['class'] }} px-2 py-1 d-inline-flex align-items-center gap-1"
                      style="font-size:9px;border-radius:5px !important;">
                    <i class="fas {{ $sourceBadge['icon'] }}"></i>
                    <span>{{ $sourceBadge['label'] }}</span>
                </span>

                @if($isTrashed)
                    <span class="badge bg-danger text-white px-2 py-1"
                          style="font-size:9px;border-radius:5px !important;">
                        <i class="fas fa-trash-can me-1"></i>Trashed
                    </span>
                @endif
            </div>
        </td>

        {{-- 4. Customer Information --}}
        <td class="py-3" style="width:165px;min-width:150px;">
            <strong class="text-dark d-block text-truncate mb-2"
                    style="font-size:12px;"
                    title="{{ $txn->customer_name ?: 'Guest Customer' }}">
                {{ $txn->customer_name ?: 'Guest Customer' }}
            </strong>

            <span class="font-monospace text-secondary d-flex align-items-center bn-digit text-truncate mb-1"
                  style="font-size:10.5px;">
                <i class="fas fa-phone-alt text-muted me-2" style="font-size:9px;"></i>
                {{ $txn->customer_phone ?: 'No Phone' }}
            </span>

            @if(!empty($txn->customer_email))
                <small class="text-muted text-truncate d-block font-monospace mt-1"
                       style="font-size:9.5px;"
                       title="{{ $txn->customer_email }}">
                    <i class="far fa-envelope me-1"></i>{{ $txn->customer_email }}
                </small>
            @endif
        </td>

        {{-- 5. Payment Method, TrxID, Sender & Proof --}}
        <td class="py-3" style="width:180px;min-width:165px;">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge bg-dark text-white font-monospace px-2 py-1"
                      style="font-size:9px;border-radius:5px !important;">
                    {{ strtoupper($txn->payment_method) }}
                </span>

                {{-- Global Media Chooser Interactive Proof Thumbnail --}}
                @if($proofUrl)
                    <div class="position-relative d-inline-flex align-items-center justify-content-center"
                         style="width:30px;height:30px;flex-shrink:0;border-radius:5px !important;overflow:hidden;border:1px solid var(--bs-border-color, #dee2e6);cursor:pointer;background:#fff;"
                         onclick="openScreenshotPreviewModal('{{ $proofUrl }}', '{{ $txn->transaction_uid }}')"
                         title="Click to Zoom Payment Proof Slip">
                        <img src="{{ $proofUrl }}"
                             alt="Proof"
                             style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.src='https://placehold.co/40x40?text=Slip';">
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold shadow-none"
                            style="font-size:9px;border-radius:5px !important;"
                            onclick="openScreenshotPreviewModal('{{ $proofUrl }}', '{{ $txn->transaction_uid }}')"
                            title="Zoom Screenshot">
                        <i class="fas fa-expand me-1"></i>Slip
                    </button>
                @else
                    <button type="button"
                            class="btn btn-sm btn-light border py-1 px-2 text-muted shadow-none d-inline-flex align-items-center gap-1"
                            style="font-size:9px;border-radius:5px !important;"
                            onclick="if(typeof chooseTransactionMediaProof === 'function'){ chooseTransactionMediaProof({{ $txn->id }}); } else { openTransactionDetailModal({{ $txn->id }}); }"
                            title="Attach Payment Proof via Global Media Library">
                        <i class="fas fa-paperclip text-primary"></i>
                        <span>Attach Proof</span>
                    </button>
                @endif
            </div>

            @if(!empty($txn->transaction_id))
                <div class="font-monospace text-dark bn-digit text-truncate mb-1"
                     style="font-size:11px;"
                     title="{{ $txn->transaction_id }}">
                    <span class="text-muted">TrxID:</span>
                    <strong class="text-primary">{{ $txn->transaction_id }}</strong>
                </div>
            @else
                <small class="text-muted d-block font-monospace mb-1" style="font-size:10px;">
                    TrxID: —
                </small>
            @endif

            @if(!empty($txn->sender_number))
                <div class="font-monospace text-secondary bn-digit text-truncate"
                     style="font-size:10px;">
                    <span class="text-muted">From:</span>
                    <strong>{{ $txn->sender_number }}</strong>
                </div>
            @endif

            @if(!empty($txn->is_duplicate_flagged))
                <span class="badge bg-danger text-white mt-2 px-2 py-1 d-inline-block font-monospace"
                      style="font-size:9px;border-radius:5px !important;">
                    <i class="fas fa-triangle-exclamation me-1"></i>Duplicate
                </span>
            @endif
        </td>

        {{-- 6. Amount Breakdown --}}
        <td class="py-3" style="width:155px;min-width:145px;">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <span class="text-muted" style="font-size:10px;">Payable</span>
                <strong class="text-dark font-monospace bn-digit" style="font-size:11px;">
                    ৳{{ number_format($expectedAmt, 2) }}
                </strong>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <span class="text-muted" style="font-size:10px;">Claimed</span>
                <span class="text-primary fw-semibold font-monospace bn-digit" style="font-size:11px;">
                    ৳{{ number_format($submittedAmt, 2) }}
                </span>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-2 border-top pt-2 mb-1">
                <span class="text-muted" style="font-size:10px;">Verified</span>
                <strong class="text-success font-monospace bn-digit" style="font-size:11px;">
                    ৳{{ number_format($verifiedAmt, 2) }}
                </strong>
            </div>

            @if($dueAmt > 0 && $status !== 'refunded')
                <div class="d-flex justify-content-between align-items-center gap-2 mt-1">
                    <span class="text-danger fw-semibold" style="font-size:10px;">Due</span>
                    <strong class="text-danger font-monospace bn-digit" style="font-size:11px;">
                        ৳{{ number_format($dueAmt, 2) }}
                    </strong>
                </div>
            @endif
        </td>

        {{-- 7. Transaction Status & Order Lifecycle --}}
        <td class="text-center py-3" style="width:155px;min-width:145px;">
            <span class="badge {{ $statusBadgeClass }} px-2 py-1 d-inline-block text-uppercase fw-bold font-monospace"
                  style="font-size:9px;border-radius:5px !important;line-height:1.5;">
                {{ $statusLabel }}
            </span>

            @if(!empty($txn->linked_order_status))
                <div class="text-muted mt-2 text-truncate"
                     style="font-size:9.5px;"
                     title="{{ str_replace('_', ' ', $txn->linked_order_status) }}">
                    Order:
                    <strong class="text-uppercase text-dark font-monospace">
                        {{ str_replace('_', ' ', $txn->linked_order_status) }}
                    </strong>
                </div>
            @endif

            @if($source === 'cod' && !empty($txn->cod_settlement_status))
                <div class="mt-2" style="font-size:9.5px;">
                    COD:
                    <span class="fw-bold font-monospace {{ $txn->cod_settlement_status === 'settled' ? 'text-success' : 'text-warning' }}">
                        {{ strtoupper($txn->cod_settlement_status) }}
                    </span>
                </div>
            @endif
        </td>

        {{-- 8. Verification Audit & Timestamps --}}
        <td class="py-3" style="width:165px;min-width:155px;">
            <div class="text-dark font-monospace bn-digit mb-2"
                 style="font-size:10.5px;">
                <i class="far fa-clock text-muted me-1"></i>
                {{ date('d M Y, h:i A', strtotime($txn->created_at)) }}
            </div>

            @if(in_array($status, ['paid', 'partially_paid']) && (!empty($txn->checker_name) || !empty($txn->verifier_name)))
                <div class="text-success fw-semibold text-truncate"
                     style="font-size:10px;"
                     title="{{ $txn->checker_name ?: $txn->verifier_name }}">
                    <i class="fas fa-circle-check me-1"></i>
                    Paid: <strong>{{ $txn->checker_name ?: $txn->verifier_name }}</strong>
                </div>
            @elseif(!empty($txn->verifier_name))
                <div class="text-success fw-semibold text-truncate"
                     style="font-size:10px;"
                     title="{{ $txn->verifier_name }}">
                    <i class="fas fa-user-check me-1"></i>
                    Verified: <strong>{{ $txn->verifier_name }}</strong>
                </div>
            @elseif(!empty($txn->checker_name))
                <div class="text-info fw-semibold text-truncate"
                     style="font-size:10px;"
                     title="{{ $txn->checker_name }}">
                    <i class="fas fa-eye me-1"></i>
                    Checked: <strong>{{ $txn->checker_name }}</strong>
                </div>
            @else
                <div class="text-danger fw-semibold" style="font-size:10px;">
                    <i class="fas fa-hourglass-half me-1"></i>Unchecked
                </div>
            @endif

            @if(!empty($txn->admin_remarks) || !empty($txn->rejection_reason))
                <div class="text-muted text-truncate mt-2 font-monospace"
                     style="max-width:150px;font-size:9.5px;"
                     title="{{ $txn->admin_remarks ?: $txn->rejection_reason }}">
                    <i class="far fa-comment-dots me-1"></i>
                    {{ $txn->admin_remarks ?: $txn->rejection_reason }}
                </div>
            @endif
        </td>

        {{-- 9. Actions --}}
        <td class="text-end pe-3 py-3" style="width:125px;min-width:115px;">
            @if($isTrashed)
                <div class="d-inline-flex align-items-center gap-1">
                    <button type="button"
                            class="btn btn-success btn-compact shadow-none py-1 px-2"
                            onclick="singleTrashOrDeleteAction({{ $txn->id }}, 'restore')"
                            title="Restore Transaction"
                            style="font-size:10px !important;border-radius:5px !important;">
                        <i class="fas fa-trash-arrow-up me-1"></i>
                        <span>Restore</span>
                    </button>

                    <button type="button"
                            class="btn btn-outline-danger btn-compact shadow-none py-1 px-2"
                            onclick="singleTrashOrDeleteAction({{ $txn->id }}, 'force_delete')"
                            title="Delete Permanently"
                            style="font-size:10px !important;border-radius:5px !important;">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @else
                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                    <button type="button"
                            class="btn btn-primary btn-compact shadow-none py-1 px-2"
                            onclick="openTransactionDetailModal({{ $txn->id }})"
                            style="font-size:10px !important;border-radius:5px !important;">
                        <i class="fas fa-shield-halved me-1"></i>
                        <span>Verify</span>
                    </button>

                    @if($isUncheckedRow || $status === 'pending_verification' || $status === 'under_review')
                        <button type="button"
                                class="btn btn-outline-info btn-compact shadow-none py-1 px-2"
                                onclick="promptVerificationAction({{ $txn->id }}, 'mark_checked', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $submittedAmt }})"
                                title="Quick Mark Checked"
                                style="font-size:10px !important;width:26px;height:26px;border-radius:5px !important;">
                            <i class="fas fa-check"></i>
                        </button>
                    @endif

                    <div class="dropdown d-inline-block">
                        <button class="btn btn-light border btn-compact shadow-none py-1 px-2"
                                type="button"
                                data-bs-toggle="dropdown"
                                data-bs-strategy="fixed"
                                aria-expanded="false"
                                style="font-size:10px !important;width:26px;height:26px;border-radius:5px !important;">
                            <i class="fas fa-ellipsis-vertical"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow border p-1"
                            style="min-width:205px;z-index:1090;font-size:11.5px;border-radius:5px !important;">

                            <li>
                                <button type="button"
                                        class="dropdown-item py-2 px-2 d-flex align-items-center gap-2"
                                        style="border-radius:5px;"
                                        onclick="openTransactionDetailModal({{ $txn->id }})">
                                    <i class="fas fa-magnifying-glass-dollar text-primary" style="width:14px;"></i>
                                    <span>Full Details &amp; Audit</span>
                                </button>
                            </li>

                            @if($isUncheckedRow || $status === 'pending_verification' || $status === 'under_review')
                                <li>
                                    <button type="button"
                                            class="dropdown-item py-2 px-2 d-flex align-items-center gap-2"
                                            style="border-radius:5px;"
                                            onclick="promptVerificationAction({{ $txn->id }}, 'mark_checked', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $submittedAmt }})">
                                        <i class="fas fa-eye text-info" style="width:14px;"></i>
                                        <span>Mark as Admin Checked</span>
                                    </button>
                                </li>
                            @endif

                            @if(in_array($status, ['pending_verification', 'checked', 'under_review']))
                                <li>
                                    <button type="button"
                                            class="dropdown-item py-2 px-2 d-flex align-items-center gap-2"
                                            style="border-radius:5px;"
                                            onclick="promptVerificationAction({{ $txn->id }}, 'approve', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $submittedAmt }})">
                                        <i class="fas fa-thumbs-up text-primary" style="width:14px;"></i>
                                        <span>Approve Transaction</span>
                                    </button>
                                </li>
                            @endif

                            @if($status !== 'paid')
                                <li>
                                    <button type="button"
                                            class="dropdown-item py-2 px-2 text-success fw-bold d-flex align-items-center gap-2"
                                            style="border-radius:5px;"
                                            onclick="promptVerificationAction({{ $txn->id }}, 'mark_paid', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $submittedAmt }})">
                                        <i class="fas fa-circle-check text-success" style="width:14px;"></i>
                                        <span>Mark Paid / Received</span>
                                    </button>
                                </li>
                            @endif

                            <li><hr class="dropdown-divider my-1"></li>

                            <li>
                                <button type="button"
                                        class="dropdown-item py-2 px-2 text-warning-emphasis d-flex align-items-center gap-2"
                                        style="border-radius:5px;"
                                        onclick="promptVerificationAction({{ $txn->id }}, 'under_review', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $submittedAmt }})">
                                    <i class="fas fa-magnifying-glass text-warning" style="width:14px;"></i>
                                    <span>Move to Under Review</span>
                                </button>
                            </li>

                            <li>
                                <button type="button"
                                        class="dropdown-item py-2 px-2 text-danger d-flex align-items-center gap-2"
                                        style="border-radius:5px;"
                                        onclick="promptVerificationAction({{ $txn->id }}, 'mark_unmatched', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $submittedAmt }})">
                                    <i class="fas fa-not-equal text-danger" style="width:14px;"></i>
                                    <span>Mark as Unmatched</span>
                                </button>
                            </li>

                            <li>
                                <button type="button"
                                        class="dropdown-item py-2 px-2 text-danger d-flex align-items-center gap-2"
                                        style="border-radius:5px;"
                                        onclick="promptVerificationAction({{ $txn->id }}, 'mark_fake', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $submittedAmt }})">
                                    <i class="fas fa-ban text-danger" style="width:14px;"></i>
                                    <span>Mark Fake / Rejected</span>
                                </button>
                            </li>

                            @if(in_array($status, ['paid', 'partially_paid']))
                                <li>
                                    <button type="button"
                                            class="dropdown-item py-2 px-2 text-secondary d-flex align-items-center gap-2"
                                            style="border-radius:5px;"
                                            onclick="promptVerificationAction({{ $txn->id }}, 'refund', '{{ $txn->transaction_uid }}', {{ $expectedAmt }}, {{ $verifiedAmt }})">
                                        <i class="fas fa-rotate-left text-secondary" style="width:14px;"></i>
                                        <span>Issue Refund</span>
                                    </button>
                                </li>
                            @endif

                            <li><hr class="dropdown-divider my-1"></li>

                            <li>
                                <button type="button"
                                        class="dropdown-item py-2 px-2 text-danger d-flex align-items-center gap-2"
                                        style="border-radius:5px;"
                                        onclick="singleTrashOrDeleteAction({{ $txn->id }}, 'trash')">
                                    <i class="fas fa-trash-can text-danger" style="width:14px;"></i>
                                    <span>Move to Trash</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </td>
    </tr>

@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="d-flex align-items-center justify-content-center rounded-circle bg-light mb-3"
                     style="width:58px;height:58px;">
                    <i class="fas fa-money-check-dollar fa-2x opacity-50"></i>
                </div>
                <span class="fw-semibold" style="font-size:12.5px;">
                    No payment transactions found matching your filter criteria.
                </span>
            </div>
        </td>
    </tr>
@endforelse

{{-- <div class="table-responsive">
    <table class="table align-middle">
        <!-- Existing table header and transaction rows -->
    </table>
</div> --}}