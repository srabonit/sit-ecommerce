@extends('layouts.admin')

@section('title', 'Blog Comments Moderation - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #commentsModerationRoot .btn,
    #commentsModerationRoot .btn-sm,
    #commentsModerationRoot button,
    #commentsModerationRoot .form-control,
    #commentsModerationRoot .form-select,
    #commentsModerationRoot .badge,
    #commentsModerationRoot .input-group-text,
    #commentsModerationRoot .page-link,
    #commentsModerationRoot .page-item span,
    #instantReplyModal .btn,
    #instantReplyModal .form-control,
    #adminDeleteModal .btn,
    #systemNoticeModal .btn {
        border-radius: 5px !important;
    }

    #commentsModerationRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #commentsModerationRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #commentsModerationRoot .btn-compact,
    #instantReplyModal .btn-compact,
    #adminDeleteModal .btn-compact,
    #systemNoticeModal .btn-compact {
        font-size: 11.5px !important;
        padding: 4px 10px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Enterprise Cards */
    .comments-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* ================= STATUS & NOTIFICATION BADGES ================= */
    .comments-table-badge { 
        font-size: 10px; 
        padding: 3px 8px; 
        font-weight: 700; 
        border-radius: 5px !important; 
        display: inline-block;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .pending-alert-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fef2f2;
        color: #dc2626;
        border-radius: 5px !important;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 11px;
        border: 1px solid #fecaca;
        white-space: nowrap;
    }
    .pulse-dot {
        width: 7px;
        height: 7px;
        background-color: #ef4444;
        border-radius: 50%;
        margin-right: 6px;
        animation: pulseAnimation 1.5s infinite;
    }
    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .auto-approve-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    /* Filter Controls */
    .comments-search-box,
    .comments-select-box {
        font-size: 12px !important;
        height: 34px !important;
        padding: 5px 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .comments-search-box:focus,
    .comments-select-box:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }
    .search-addon-icon {
        height: 34px !important;
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-right: 0 !important;
        color: #64748b !important;
    }

    /* Table Styling */
    .custom-comments-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 10px 14px;
        white-space: nowrap;
    }
    .custom-comments-table td {
        padding: 10px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-comments-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* ================= ACTION BUTTONS (SLEEK 5PX SQUARE) ================= */
    .comment-actions-wrap {
        display: inline-flex;
        justify-content: flex-end;
        align-items: center;
        gap: 6px !important;
    }
    .comment-action-btn,
    .comment-actions-wrap .btn,
    .comment-actions-wrap button {
        width: 27px !important;
        height: 27px !important;
        min-width: 27px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        line-height: 1 !important;
        transition: all 0.15s ease !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .comment-actions-wrap .btn:hover,
    .comment-actions-wrap button:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(15, 23, 42, 0.08);
    }

    /* Pagination Styling */
    #paginationWrap nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px;
        width: 100% !important;
    }
    @media (min-width: 768px) {
        #paginationWrap nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }
    #paginationWrap nav svg {
        width: 14px !important;
        height: 14px !important;
    }
    #paginationWrap .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 4px;
        padding-left: 0;
        list-style: none;
    }
    #paginationWrap .page-item .page-link,
    #paginationWrap .page-item span {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 30px;
        height: 30px;
        padding: 3px 8px;
        font-size: 12px;
        color: #475569;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        text-decoration: none;
    }
    #paginationWrap .page-item.active .page-link,
    #paginationWrap .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    /* ================= 100% MOBILE RESPONSIVE TUNING ================= */
    @media (max-width: 767.98px) {
        .comments-header-title {
            font-size: 16px !important;
        }
        .header-controls-bar {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .auto-approve-card {
            flex: 1 1 auto;
            justify-content: space-between;
            padding: 6px 10px;
        }
        .modal-dialog {
            margin: 12px auto !important;
            max-width: 94% !important;
        }
        .custom-comments-table th,
        .custom-comments-table td {
            padding: 9px 10px !important;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="commentsModerationRoot">
    <!-- Header with Pending Alert & Auto Approve Toggle -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 comments-header-title" style="font-size: 18px;">Blog Comments Moderation</h5>
                @if($pendingCount > 0)
                    <div class="pending-alert-badge" id="pendingAlertBadge">
                        <span class="pulse-dot"></span>
                        <span id="pendingCountNum" class="bn-digit me-1">{{ $pendingCount }}</span>
                        <span>Pending Approval</span>
                    </div>
                @else
                    <div class="pending-alert-badge bg-light text-muted border" id="pendingAlertBadge">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <span>All caught up</span>
                    </div>
                @endif
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Review, instant-reply, approve, filter, and moderate user discussions.</small>
        </div>

        <div class="header-controls-bar d-flex align-items-center gap-2 flex-wrap justify-content-between justify-content-lg-end">
            <!-- Auto Approve Switch Control -->
            <div class="auto-approve-card shadow-xs">
                <div class="form-check form-switch m-0 p-0 d-flex align-items-center justify-content-between w-100 gap-2">
                    <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="toggleAutoApproveSwitch" style="font-size: 11.5px;">Auto-Approve Comments</label>
                    <input class="form-check-input m-0 cursor-pointer" type="checkbox" id="toggleAutoApproveSwitch" {{ $autoApproveSetting == '1' ? 'checked' : '' }} onchange="toggleAutoApprove(this)">
                </div>
            </div>
            <div class="text-muted small bg-white border px-2 py-1 flex-shrink-0" style="font-size: 11.5px; border-radius: 5px;">
                Total: <strong class="text-dark font-monospace bn-digit" id="totalCommentsCount">{{ $comments->total() ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="comments-panel-card p-3 mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text search-addon-icon py-1"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                    <input type="text" id="ajaxSearchInput" class="form-control comments-search-box" placeholder="Search by name, email, phone, comment text or post ID..." oninput="triggerAjaxFilter()">
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <select id="statusFilter" class="form-select comments-select-box" onchange="triggerAjaxFilter()">
                    <option value="all">Status: All Comments</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="spam">Spam</option>
                </select>
            </div>
            <div class="col-6 col-lg-4">
                <select id="sortFilter" class="form-select comments-select-box" onchange="triggerAjaxFilter()">
                    <option value="recent_comment">Sort: Recent Comments First</option>
                    <option value="old_comment">Sort: Oldest Comments First</option>
                    <option value="recent_post">Recent Post Comments</option>
                    <option value="old_post">Old Post Comments</option>
                    <option value="new_user">New User Comments</option>
                    <option value="old_user">Old User Comments</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="comments-panel-card overflow-hidden position-relative">
        <!-- Loading Overlay -->
        <div id="tableLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 5;">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>

        <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
            <table class="table custom-comments-table align-middle mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4">Author</th>
                        <th>Comment Text</th>
                        <th>In Response To</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Submitted</th>
                        <th class="text-end pe-3 pe-md-4" style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody id="commentsTbody">
                    @include('admin.content.blogs.partials.comments_table_rows')
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top bg-light bg-opacity-50 d-flex justify-content-center overflow-auto" id="paginationWrap">
            @if(method_exists($comments, 'hasPages') &&$comments->hasPages())
                {{ $comments->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>
</div>

<!-- ================= 1. INSTANT REPLY MODAL ================= -->
<div class="modal fade" id="instantReplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 8px !important;">
            <div class="modal-header border-bottom py-2 px-3 bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 13.5px;">
                    <i class="fas fa-reply text-primary me-2"></i>
                    <span>Instant Reply to Comment</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="bg-light p-2 mb-3 border" style="border-radius: 5px;">
                    <small class="text-muted d-block mb-1" style="font-size: 11px;">Replying to <strong id="replyToAuthor" class="text-dark"></strong>:</small>
                    <p id="replyToCommentText" class="mb-0 small text-secondary fst-italic text-wrap" style="font-size: 12px; line-height: 1.4;"></p>
                </div>
                <input type="hidden" id="replyTargetCommentId">
                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">Admin Reply:</label>
                <textarea id="adminReplyText" class="form-control shadow-none" rows="3" placeholder="Type official response..." required style="font-size: 12.5px;"></textarea>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-compact px-3 shadow-xs" id="btnSendAdminReply" onclick="submitAdminReply()">
                    <i class="fas fa-paper-plane me-1"></i>
                    <span>Send Reply</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= 2. DELETE CONFIRMATION MODAL ================= -->
<div class="modal fade" id="adminDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg overflow-hidden text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 48px; height: 48px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Comment?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">This comment and all nested replies will be permanently deleted.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="adminDeleteForm" method="POST" action="" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact px-3 shadow-xs">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= 3. SYSTEM NOTIFICATION MODAL ================= -->
<div class="modal fade" id="systemNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div id="systemNoticeIconWrap" class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 48px; height: 48px;">
                    <i id="systemNoticeIcon" class="fas fa-check-circle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="systemNoticeTitle" style="font-size: 14.5px;">Notice</h6>
                <p class="text-muted small mb-3" id="systemNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    let filterDebounceTimer = null;

    function showSystemNotice(title, message, isError = false) {
        document.getElementById('systemNoticeTitle').innerText = title;
        document.getElementById('systemNoticeMsg').innerText = message;
        const wrap = document.getElementById('systemNoticeIconWrap');
        const icon = document.getElementById('systemNoticeIcon');
        if (wrap && icon) {
            wrap.className = isError
                ? 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2'
                : 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            icon.className = isError ? 'fas fa-exclamation-circle fs-4' : 'fas fa-check-circle fs-4';
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('systemNoticeModal')).show();
    }

    function triggerAjaxFilter() {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(() => {
            loadCommentsAjax(1);
        }, 300);
    }

    function loadCommentsAjax(page = 1) {
        const q = document.getElementById('ajaxSearchInput').value;
        const status = document.getElementById('statusFilter').value;
        const sort = document.getElementById('sortFilter').value;
        const overlay = document.getElementById('tableLoadingOverlay');

        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');

        fetch(`{{ route('admin.content.blogs.comments') }}?q=${encodeURIComponent(q)}&status=${status}&sort=${sort}&page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            overlay.classList.add('d-none');
            overlay.classList.remove('d-flex');

            document.getElementById('commentsTbody').innerHTML = data.html;
            document.getElementById('paginationWrap').innerHTML = data.pagination;
            document.getElementById('totalCommentsCount').innerText = data.total;

            const pendingBadge = document.getElementById('pendingAlertBadge');
            if (data.pending_count > 0) {
                pendingBadge.className = 'pending-alert-badge';
                pendingBadge.innerHTML = `<span class="pulse-dot"></span><span class="bn-digit me-1">${data.pending_count}</span><span>Pending Approval</span>`;
            } else {
                pendingBadge.className = 'pending-alert-badge bg-light text-muted border';
                pendingBadge.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i><span>All caught up</span>`;
            }
        })
        .catch(() => {
            overlay.classList.add('d-none');
            overlay.classList.remove('d-flex');
        });
    }

    // Pagination Click Interceptor
    document.addEventListener('click', function(e) {
        const link = e.target.closest('#paginationWrap .page-link');
        if (link) {
            const href = link.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                e.preventDefault();
                const urlObj = new URL(href, window.location.origin);
                const page = urlObj.searchParams.get('page') || 1;
                loadCommentsAjax(page);
            }
        }
    });

    // Auto-Approve Toggle Function
    function toggleAutoApprove(inputEl) {
        const isChecked = inputEl.checked;
        fetch("{{ route('admin.content.blogs.comments.toggleAutoApprove') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ auto_approve: isChecked })
        })
        .then(res => res.json())
        .then(data => {
            showSystemNotice('Setting Updated', data.message, false);
        });
    }

    // Instant Reply Modal
    function openReplyModal(commentId, authorName, commentText) {
        document.getElementById('replyTargetCommentId').value = commentId;
        document.getElementById('replyToAuthor').innerText = authorName;
        document.getElementById('replyToCommentText').innerText = '"' + commentText + '"';
        document.getElementById('adminReplyText').value = '';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('instantReplyModal')).show();
    }

    function submitAdminReply() {
        const id = document.getElementById('replyTargetCommentId').value;
        const reply = document.getElementById('adminReplyText').value.trim();
        const btn = document.getElementById('btnSendAdminReply');

        if (!reply) {
            showSystemNotice('Reply Required', 'Please enter reply text before sending.', true);
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span><span>Sending...</span>';

        fetch(`{{ url('admin/content/blogs/comment') }}/${id}/reply`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reply: reply })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i><span>Send Reply</span>';
            bootstrap.Modal.getInstance(document.getElementById('instantReplyModal'))?.hide();

            showSystemNotice('Replied', data.message, false);
            loadCommentsAjax(1);
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i><span>Send Reply</span>';
            showSystemNotice('Error', 'Failed to send reply. Please try again.', true);
        });
    }

    function openDeleteModal(actionUrl) {
        document.getElementById('adminDeleteForm').action = actionUrl;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('adminDeleteModal')).show();
    }
</script>
@endsection