@forelse($comments as $item)
    <tr id="comment-row-{{ $item->id }}" class="align-middle">

        {{-- Author Details --}}
        <td class="ps-3 ps-md-4 py-3" style="min-width: 180px;">
            <div class="d-flex flex-column gap-1">
                <span class="fw-semibold text-dark" style="font-size: 13px; line-height: 1.35;">
                    {{ $item->user_name }}
                </span>

                <span class="text-muted text-truncate" style="font-size: 11.5px; max-width: 180px;">
                    <i class="far fa-envelope me-1 opacity-75"></i>
                    {{ $item->user_email }}
                </span>

                @if(!empty($item->user_phone))
                    <span class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        <i class="fas fa-phone-alt me-1 opacity-75"></i>
                        {{ $item->user_phone }}
                    </span>
                @endif
            </div>
        </td>

        {{-- Comment Content --}}
        <td class="py-3" style="min-width: 240px; max-width: 340px;">
            <div class="text-dark text-break" style="font-size: 12.5px; line-height: 1.6;">
                {{ $item->comment }}
            </div>

            @if(!empty($item->parent_id) || !empty($item->is_edited))
                <div class="d-flex flex-wrap align-items-center gap-1 mt-2">

                    @if(!empty($item->parent_id))
                        <span class="badge bg-light text-primary border px-2 py-1 bn-digit"
                              style="font-size: 10px; font-weight: 500;">
                            <i class="fas fa-reply me-1"></i>
                            Replied to #{{ $item->parent_id }}
                        </span>
                    @endif

                    @if(!empty($item->is_edited))
                        <span class="badge bg-light text-muted border px-2 py-1"
                              style="font-size: 10px; font-weight: 500;">
                            <i class="fas fa-pen me-1"></i>
                            Edited
                        </span>
                    @endif

                </div>
            @endif
        </td>

        {{-- In Response To Post --}}
        <td class="py-3" style="min-width: 180px;">
            <div class="fw-semibold text-dark text-truncate mb-2"
                 style="max-width: 200px; font-size: 12.5px;"
                 title="{{ $item->post_title }}">
                {{ $item->post_title }}
            </div>

            <a href="{{ route('blogs.show', $item->post_slug) }}"
               target="_blank"
               class="d-inline-flex align-items-center text-primary text-decoration-none"
               style="font-size: 11.5px;">

                <i class="fas fa-external-link-alt me-1" style="font-size: 10px;"></i>
                View Post
                <span class="ms-1 text-muted bn-digit">
                    (#{{ $item->blog_post_id }})
                </span>
            </a>
        </td>

        {{-- Status --}}
        <td class="text-center py-3 text-nowrap">
            @if($item->status == 1)
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-2 text-uppercase"
                      style="font-size: 9.5px; font-weight: 700; letter-spacing: .35px;">
                    <i class="fas fa-check-circle me-1"></i>
                    Approved
                </span>
            @elseif($item->status == 2)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-2 text-uppercase"
                      style="font-size: 9.5px; font-weight: 700; letter-spacing: .35px;">
                    <i class="fas fa-ban me-1"></i>
                    Spam
                </span>
            @else
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-2 text-uppercase"
                      style="font-size: 9.5px; font-weight: 700; letter-spacing: .35px;">
                    <i class="fas fa-clock me-1"></i>
                    Waiting Approval
                </span>
            @endif
        </td>

        {{-- Submitted Timestamp --}}
        <td class="text-end py-3 text-nowrap font-monospace bn-digit">
            <div class="text-dark fw-semibold" style="font-size: 11.5px;">
                {{ \Carbon\Carbon::parse($item->created_at)->format('d M, Y') }}
            </div>

            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                <i class="far fa-clock me-1"></i>
                {{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}
            </small>
        </td>

        {{-- Action Buttons --}}
        <td class="text-end pe-3 pe-md-4 py-3 text-nowrap">
            <div class="d-inline-flex align-items-center justify-content-end gap-1">

                {{-- Instant Reply --}}
                <button type="button"
                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center p-0"
                        style="width: 29px; height: 29px; border-radius: 5px;"
                        title="Reply to comment"
                        onclick="openReplyModal({{ $item->id }}, '{{ addslashes($item->user_name) }}', '{{ addslashes($item->comment) }}')">

                    <i class="fas fa-reply" style="font-size: 11px;"></i>
                </button>

                {{-- Approve --}}
                @if($item->status != 1)
                    <form action="{{ route('admin.content.blogs.comment.status', $item->id) }}"
                          method="POST"
                          class="d-inline m-0 p-0">

                        @csrf
                        <input type="hidden" name="status" value="1">

                        <button type="submit"
                                class="btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center p-0"
                                style="width: 29px; height: 29px; border-radius: 5px;"
                                title="Approve">

                            <i class="fas fa-check" style="font-size: 11px;"></i>
                        </button>
                    </form>
                @endif

                {{-- Mark as Spam --}}
                @if($item->status != 2)
                    <form action="{{ route('admin.content.blogs.comment.status', $item->id) }}"
                          method="POST"
                          class="d-inline m-0 p-0">

                        @csrf
                        <input type="hidden" name="status" value="2">

                        <button type="submit"
                                class="btn btn-sm btn-outline-warning d-inline-flex align-items-center justify-content-center p-0"
                                style="width: 29px; height: 29px; border-radius: 5px;"
                                title="Mark as Spam">

                            <i class="fas fa-ban" style="font-size: 11px;"></i>
                        </button>
                    </form>
                @endif

                {{-- Delete --}}
                <button type="button"
                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center p-0"
                        style="width: 29px; height: 29px; border-radius: 5px;"
                        title="Delete"
                        onclick="openDeleteModal('{{ route('admin.content.blogs.comment.destroy', $item->id) }}')">

                    <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
                </button>

            </div>
        </td>

    </tr>

@empty
    {{-- Empty State --}}
    <tr>
        <td colspan="6" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center py-3">

                <div class="d-flex align-items-center justify-content-center rounded-circle bg-light mb-3"
                     style="width: 58px; height: 58px;">

                    <i class="far fa-comments text-muted" style="font-size: 25px;"></i>
                </div>

                <h6 class="fw-semibold text-dark mb-1" style="font-size: 13px;">
                    No Matching Comments Found
                </h6>

                <p class="text-muted mb-0" style="font-size: 12px;">
                    There are no comments available for the selected filters.
                </p>

            </div>
        </td>
    </tr>
@endforelse