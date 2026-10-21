@forelse($comments as $item)
    <tr id="comment-row-{{ $item->id }}" class="align-middle">
        <!-- Author Details -->
        <td class="ps-3 ps-md-4 py-2" style="min-width: 170px;">
            <strong class="text-dark d-block mb-1" style="font-size: 13px; line-height: 1.25;">{{ $item->user_name }}</strong>
            <small class="text-muted d-block text-truncate" style="font-size: 11px; max-width: 170px;">{{ $item->user_email }}</small>
            @if(!empty($item->user_phone))
                <small class="text-muted d-block font-monospace bn-digit mt-1" style="font-size: 10.5px;">
                    <i class="fas fa-phone-alt me-1 opacity-75" style="font-size: 9px;"></i>{{ $item->user_phone }}
                </small>
            @endif
        </td>

        <!-- Comment Content -->
        <td class="py-2 text-wrap" style="min-width: 220px; max-width: 320px;">
            <span class="small text-secondary fw-normal d-block text-break" style="font-size: 12.5px; line-height: 1.45;">{{ $item->comment }}</span>
            @if(!empty($item->parent_id) || !empty($item->is_edited))
                <div class="d-flex align-items-center gap-1 mt-1">
                    @if(!empty($item->parent_id))
                        <span class="badge bg-light text-primary border px-2 py-1 bn-digit" style="font-size: 9px; border-radius: 5px !important;">
                            <i class="fas fa-reply me-1"></i>Replied to #{{ $item->parent_id }}
                        </span>
                    @endif
                    @if(!empty($item->is_edited))
                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 9px; border-radius: 5px !important;">edited</span>
                    @endif
                </div>
            @endif
        </td>

        <!-- In Response To Post -->
        <td class="py-2" style="min-width: 170px;">
            <div class="small fw-semibold text-dark text-truncate mb-1" style="max-width: 190px; font-size: 12.5px;">{{ $item->post_title }}</div>
            <a href="{{ route('blogs.show', $item->post_slug) }}" target="_blank" class="small text-decoration-none text-primary d-inline-flex align-items-center bn-digit" style="font-size: 11px;">
                <i class="fas fa-external-link-alt me-1" style="font-size: 9.5px;"></i>View Post (ID: {{ $item->blog_post_id }})
            </a>
        </td>

        <!-- Status Badge -->
        <td class="text-center py-2 text-nowrap">
            @if($item->status == 1)
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 text-uppercase fw-bold" style="font-size: 9.5px; letter-spacing: 0.03em; border-radius: 5px !important;">Approved</span>
            @elseif($item->status == 2)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 text-uppercase fw-bold" style="font-size: 9.5px; letter-spacing: 0.03em; border-radius: 5px !important;">Spam</span>
            @else
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 text-uppercase fw-bold" style="font-size: 9.5px; letter-spacing: 0.03em; border-radius: 5px !important;">Waiting Approval</span>
            @endif
        </td>

        <!-- Submitted Timestamp -->
        <td class="text-end text-muted py-2 text-nowrap font-monospace bn-digit" style="font-size: 11.5px;">
            <div>{{ \Carbon\Carbon::parse($item->created_at)->format('d M, Y') }}</div>
            <small class="text-muted opacity-75 d-block mt-1" style="font-size: 10px;">{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</small>
        </td>

        <!-- Action Buttons (Strictly 5px Compact Square Buttons) -->
        <td class="text-end pe-3 pe-md-4 py-2 text-nowrap">
            <div class="d-inline-flex justify-content-end align-items-center gap-1">
                <!-- Instant Reply Button -->
                <button type="button" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                        style="width: 26px; height: 26px; border-radius: 5px !important;"
                        title="Reply to comment" onclick="openReplyModal({{ $item->id }}, '{{ addslashes($item->user_name) }}', '{{ addslashes($item->comment) }}')">
                    <i class="fas fa-reply" style="font-size: 10px;"></i>
                </button>

                @if($item->status != 1)
                    <form action="{{ route('admin.content.blogs.comment.status', $item->id) }}" method="POST" class="d-inline m-0 p-0">
                        @csrf
                        <input type="hidden" name="status" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-success p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                                style="width: 26px; height: 26px; border-radius: 5px !important;"
                                title="Approve">
                            <i class="fas fa-check" style="font-size: 10px;"></i>
                        </button>
                    </form>
                @endif

                @if($item->status != 2)
                    <form action="{{ route('admin.content.blogs.comment.status', $item->id) }}" method="POST" class="d-inline m-0 p-0">
                        @csrf
                        <input type="hidden" name="status" value="2">
                        <button type="submit" class="btn btn-sm btn-outline-warning p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                                style="width: 26px; height: 26px; border-radius: 5px !important;"
                                title="Mark as Spam">
                            <i class="fas fa-ban" style="font-size: 10px;"></i>
                        </button>
                    </form>
                @endif

                <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                        style="width: 26px; height: 26px; border-radius: 5px !important;"
                        title="Delete" onclick="openDeleteModal('{{ route('admin.content.blogs.comment.destroy', $item->id) }}')">
                    <i class="fas fa-trash-alt" style="font-size: 10px;"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5 text-muted small">
            <i class="far fa-comments fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No matching comments found.</span>
        </td>
    </tr>
@endforelse