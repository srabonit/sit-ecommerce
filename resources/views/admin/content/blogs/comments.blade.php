@extends('layouts.admin')

@section('title', 'Blog Comments Moderation - ' . config('app.name'))

@section('admin_content')

<style>
    /* =========================================================
       BLOG COMMENTS MODERATION — KAIADMIN CLASSIC
    ========================================================= */

    #commentsModerationRoot {
        --comments-primary: var(--bs-primary, #2563eb);
        --comments-dark: #344767;
        --comments-muted: #8392ab;
        --comments-border: #e9ecef;
        --comments-surface: #ffffff;
        --comments-soft: #f8f9fa;
        --comments-success: #2dce89;
        --comments-danger: #f5365c;
        --comments-warning: #fb6340;
        color: var(--comments-dark);
        font-size: 13px;
    }

    #commentsModerationRoot *,
    #instantReplyModal *,
    #adminDeleteModal *,
    #systemNoticeModal * {
        box-sizing: border-box;
    }

    /* Consistent Radius */

    #commentsModerationRoot .btn,
    #commentsModerationRoot .form-control,
    #commentsModerationRoot .form-select,
    #commentsModerationRoot .badge,
    #commentsModerationRoot .input-group-text,
    #commentsModerationRoot .page-link,
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

    /* Panel */

    #commentsModerationRoot .comments-panel-card {
        background: var(--comments-surface);
        border: 1px solid var(--comments-border);
        border-radius: 7px !important;
        box-shadow: 0 2px 5px rgba(50, 50, 93, 0.04);
    }

    /* Header */

    #commentsModerationRoot .comments-header-title {
        color: var(--comments-dark);
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    #commentsModerationRoot .comments-header-description {
        color: var(--comments-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    #commentsModerationRoot .header-controls-bar {
        gap: 10px;
    }

    /* Pending Badge */

    #commentsModerationRoot .pending-alert-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        background: #fff5f5;
        color: #e53935;
        border: 1px solid #ffe0e0;
        border-radius: 5px !important;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    #commentsModerationRoot .pulse-dot {
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        background: #ef4444;
        border-radius: 50%;
        animation: pulseAnimation 1.5s infinite;
    }

    @keyframes pulseAnimation {
        0% {
            transform: scale(.95);
            box-shadow: 0 0 0 0 rgba(239, 68, 68, .5);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }
        100% {
            transform: scale(.95);
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }

    /* Auto Approve */

    #commentsModerationRoot .auto-approve-card {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        background: #fff;
        border: 1px solid var(--comments-border);
        border-radius: 5px !important;
        min-height: 36px;
    }

    #commentsModerationRoot .auto-approve-card .form-check-label {
        color: var(--comments-dark);
        font-size: 11.5px;
        font-weight: 600;
    }

    #commentsModerationRoot .auto-approve-card .form-check-input {
        width: 2em;
        height: 1em;
        margin-top: 0;
        cursor: pointer;
    }

    #commentsModerationRoot .total-count-card {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 36px;
        padding: 6px 11px;
        background: var(--comments-soft);
        border: 1px solid var(--comments-border);
        border-radius: 5px;
        color: var(--comments-muted);
        font-size: 11.5px;
        white-space: nowrap;
    }

    #commentsModerationRoot .total-count-card strong {
        color: var(--comments-dark);
        font-size: 13px;
        font-weight: 700;
    }

    /* Toolbar */

    #commentsModerationRoot .comments-toolbar {
        padding: 14px;
    }

    #commentsModerationRoot .comments-search-box,
    #commentsModerationRoot .comments-select-box {
        height: 36px !important;
        padding: 6px 10px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #fff !important;
        color: #344767 !important;
        font-size: 12px !important;
        box-shadow: none !important;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    #commentsModerationRoot .comments-search-box::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #commentsModerationRoot .comments-search-box:focus,
    #commentsModerationRoot .comments-select-box:focus {
        border-color: var(--comments-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .09) !important;
    }

    #commentsModerationRoot .search-addon-icon {
        min-width: 38px;
        justify-content: center;
        background: #f8f9fa !important;
        border: 1px solid #e2e8f0 !important;
        border-right: 0 !important;
        color: #8392ab !important;
    }

    /* Table */

    #commentsModerationRoot .comments-table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #commentsModerationRoot .custom-comments-table {
        width: 100%;
        min-width: 900px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #commentsModerationRoot .custom-comments-table thead th {
        padding: 12px 14px;
        background: #f8f9fa;
        color: #8392ab;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .045em;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
        vertical-align: middle;
    }

    #commentsModerationRoot .custom-comments-table tbody td {
        padding: 12px 14px;
        color: #344767;
        font-size: 12px;
        border-bottom: 1px solid #f1f3f5;
        vertical-align: middle;
    }

    #commentsModerationRoot .custom-comments-table tbody tr {
        transition: background-color .15s ease;
    }

    #commentsModerationRoot .custom-comments-table tbody tr:hover {
        background-color: #fafbfc;
    }

    #commentsModerationRoot .custom-comments-table tbody tr:last-child td {
        border-bottom: 0;
    }

    /* Table Badges */

    #commentsModerationRoot .comments-table-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 5px !important;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    /* Action Buttons */

    #commentsModerationRoot .comment-actions-wrap {
        display: inline-flex;
        justify-content: flex-end;
        align-items: center;
        gap: 6px;
    }

    #commentsModerationRoot .comment-action-btn,
    #commentsModerationRoot .comment-actions-wrap .btn,
    #commentsModerationRoot .comment-actions-wrap button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 29px !important;
        height: 29px !important;
        min-width: 29px !important;
        padding: 0 !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        line-height: 1 !important;
        transition: all .18s ease !important;
        box-shadow: none;
    }

    #commentsModerationRoot .comment-actions-wrap .btn:hover,
    #commentsModerationRoot .comment-actions-wrap button:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 7px rgba(50, 50, 93, .1);
    }

    /* Pagination */

    #commentsModerationRoot #paginationWrap {
        min-height: 58px;
        padding: 12px 16px !important;
        background: #fff !important;
        border-top: 1px solid var(--comments-border) !important;
    }

    #commentsModerationRoot #paginationWrap nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px;
        width: 100% !important;
    }

    #commentsModerationRoot #paginationWrap nav svg {
        width: 14px !important;
        height: 14px !important;
    }

    #commentsModerationRoot #paginationWrap .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 4px;
        padding: 0;
        margin: 0 !important;
        list-style: none;
    }

    #commentsModerationRoot #paginationWrap .page-item .page-link,
    #commentsModerationRoot #paginationWrap .page-item span {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 30px;
        height: 30px;
        padding: 3px 8px;
        color: #525f7f;
        background: #fff;
        border: 1px solid #e9ecef;
        font-size: 11.5px;
        text-decoration: none;
    }

    #commentsModerationRoot #paginationWrap .page-item.active .page-link,
    #commentsModerationRoot #paginationWrap .page-item.active span {
        color: #fff !important;
        background: var(--comments-primary) !important;
        border-color: var(--comments-primary) !important;
        font-weight: 700;
    }

    /* Loading Overlay */

    #commentsModerationRoot #tableLoadingOverlay {
        z-index: 5;
        backdrop-filter: blur(1px);
    }

    #commentsModerationRoot #tableLoadingOverlay .spinner-border {
        width: 26px;
        height: 26px;
    }

    /* Common */

    #commentsModerationRoot .bn-digit,
    #commentsModerationRoot .font-monospace {
        font-weight: 700 !important;
    }

    #commentsModerationRoot .cursor-pointer {
        cursor: pointer;
    }

    /* Modals */

    #instantReplyModal .modal-content,
    #adminDeleteModal .modal-content,
    #systemNoticeModal .modal-content {
        border-radius: 8px !important;
    }

    #instantReplyModal .modal-header,
    #instantReplyModal .modal-footer {
        background: #fff;
        border-color: #edf0f2 !important;
    }

    #instantReplyModal .modal-title {
        font-size: 13.5px;
    }

    #instantReplyModal .modal-body {
        padding: 16px;
    }

    #instantReplyModal textarea {
        min-height: 90px;
        border: 1px solid #e2e8f0;
        font-size: 12.5px;
        resize: vertical;
    }

    #instantReplyModal textarea:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .09) !important;
    }

    /* Compact Buttons */

    #commentsModerationRoot .btn-compact,
    #instantReplyModal .btn-compact,
    #adminDeleteModal .btn-compact,
    #systemNoticeModal .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        white-space: nowrap;
        transition: all .15s ease;
    }

    /* Responsive */

    @media (min-width: 768px) {
        #commentsModerationRoot #paginationWrap nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }

    @media (max-width: 991.98px) {
        #commentsModerationRoot .header-controls-bar {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        #commentsModerationRoot {
            font-size: 12px;
        }

        #commentsModerationRoot .comments-header-title {
            font-size: 16px !important;
        }

        #commentsModerationRoot .header-controls-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        #commentsModerationRoot .auto-approve-card {
            flex: 1 1 auto;
            min-width: 0;
            padding: 6px 9px;
        }

        #commentsModerationRoot .auto-approve-card .form-check-label {
            font-size: 10.5px;
        }

        #commentsModerationRoot .comments-toolbar {
            padding: 11px;
        }

        #commentsModerationRoot .custom-comments-table thead th,
        #commentsModerationRoot .custom-comments-table tbody td {
            padding: 10px !important;
            font-size: 11.5px;
        }

        #commentsModerationRoot #paginationWrap {
            padding: 12px 10px !important;
        }

        #instantReplyModal .modal-dialog,
        #adminDeleteModal .modal-dialog,
        #systemNoticeModal .modal-dialog {
            margin: 12px auto !important;
            max-width: 94% !important;
        }
    }

    @media (max-width: 380px) {
        #commentsModerationRoot .pending-alert-badge {
            font-size: 9px;
            padding: 4px 7px;
        }

        #commentsModerationRoot .auto-approve-card .form-check-label {
            font-size: 10px;
        }

        #commentsModerationRoot .total-count-card {
            padding: 5px 8px;
            font-size: 10.5px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #commentsModerationRoot *,
        #instantReplyModal *,
        #adminDeleteModal *,
        #systemNoticeModal * {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="commentsModerationRoot">

    {{-- =====================================================
         PAGE HEADER
    ====================================================== --}}

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-3">

        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">

                <h5 class="comments-header-title mb-0">
                    Blog Comments Moderation
                </h5>

                @if($pendingCount > 0)
                    <div class="pending-alert-badge" id="pendingAlertBadge">
                        <span class="pulse-dot"></span>

                        <span id="pendingCountNum" class="bn-digit">
                            {{ $pendingCount }}
                        </span>

                        <span>Pending Approval</span>
                    </div>
                @else
                    <div class="pending-alert-badge bg-light text-muted border" id="pendingAlertBadge">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>All caught up</span>
                    </div>
                @endif

            </div>

            <small class="comments-header-description d-block mt-1">
                Review, instant-reply, approve, filter, and moderate user discussions.
            </small>
        </div>

        <div class="header-controls-bar d-flex align-items-center flex-wrap justify-content-between justify-content-lg-end">

            {{-- Auto Approve Switch --}}

            <div class="auto-approve-card shadow-sm">

                <div class="form-check form-switch m-0 p-0 d-flex align-items-center justify-content-between w-100 gap-2">

                    <label class="form-check-label cursor-pointer" for="toggleAutoApproveSwitch">
                        Auto-Approve Comments
                    </label>

                    <input
                        class="form-check-input m-0 cursor-pointer"
                        type="checkbox"
                        id="toggleAutoApproveSwitch"
                        {{ $autoApproveSetting == '1' ? 'checked' : '' }}
                        onchange="toggleAutoApprove(this)"
                    >

                </div>

            </div>

            {{-- Total Comments --}}

            <div class="total-count-card flex-shrink-0">
                <span>Total:</span>

                <strong class="font-monospace bn-digit" id="totalCommentsCount">
                    {{ $comments->total() ?? 0 }}
                </strong>
            </div>

        </div>

    </div>

    {{-- =====================================================
         SEARCH & FILTER TOOLBAR
    ====================================================== --}}

    <div class="comments-panel-card comments-toolbar mb-3">

        <div class="row g-2 align-items-center">

            {{-- Search --}}

            <div class="col-12 col-lg-5">

                <div class="input-group">

                    <span class="input-group-text search-addon-icon">
                        <i class="fas fa-search" style="font-size: 11px;"></i>
                    </span>

                    <input
                        type="text"
                        id="ajaxSearchInput"
                        class="form-control comments-search-box"
                        placeholder="Search by name, email, phone, comment text or post ID..."
                        oninput="triggerAjaxFilter()"
                    >

                </div>

            </div>

            {{-- Status Filter --}}

            <div class="col-6 col-lg-3">

                <select
                    id="statusFilter"
                    class="form-select comments-select-box"
                    onchange="triggerAjaxFilter()"
                >
                    <option value="all">Status: All Comments</option>

                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                        Pending Approval
                    </option>

                    <option value="approved">
                        Approved
                    </option>

                    <option value="spam">
                        Spam
                    </option>
                </select>

            </div>

            {{-- Sort Filter --}}

            <div class="col-6 col-lg-4">

                <select
                    id="sortFilter"
                    class="form-select comments-select-box"
                    onchange="triggerAjaxFilter()"
                >
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

    {{-- =====================================================
         COMMENTS TABLE
    ====================================================== --}}

    <div class="comments-panel-card overflow-hidden position-relative">

        {{-- Loading Overlay --}}

        <div
            id="tableLoadingOverlay"
            class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center"
            style="z-index: 5;"
        >
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        {{-- Table --}}

        <div class="comments-table-wrapper table-responsive">

            <table class="table custom-comments-table align-middle text-nowrap">

                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4">Author</th>
                        <th>Comment Text</th>
                        <th>In Response To</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Submitted</th>
                        <th class="text-end pe-3 pe-md-4" style="width: 140px;">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody id="commentsTbody">
                    @include('admin.content.blogs.partials.comments_table_rows')
                </tbody>

            </table>

        </div>

        {{-- Pagination --}}

        <div
            class="d-flex justify-content-center overflow-auto"
            id="paginationWrap"
        >
            @if(method_exists($comments, 'hasPages') && $comments->hasPages())
                {{ $comments->links('pagination::bootstrap-5') }}
            @endif
        </div>

    </div>

</div>


{{-- =========================================================
     1. INSTANT REPLY MODAL
========================================================= --}}

<div class="modal fade" id="instantReplyModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">

        <div class="modal-content border-0 shadow-lg overflow-hidden">

            <div class="modal-header border-bottom py-3 px-3">

                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center">

                    <i class="fas fa-reply text-primary me-2"></i>

                    <span>Instant Reply to Comment</span>

                </h6>

                <button
                    type="button"
                    class="btn-close btn-sm shadow-none"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <div class="modal-body">

                {{-- Original Comment --}}

                <div class="bg-light p-3 mb-3 border rounded">

                    <small class="text-muted d-block mb-1">
                        Replying to
                        <strong id="replyToAuthor" class="text-dark"></strong>:
                    </small>

                    <p
                        id="replyToCommentText"
                        class="mb-0 small text-secondary fst-italic text-wrap"
                        style="font-size: 12px; line-height: 1.5;"
                    ></p>

                </div>

                <input type="hidden" id="replyTargetCommentId">

                <label class="form-label small fw-semibold text-dark mb-2">
                    Admin Reply:
                </label>

                <textarea
                    id="adminReplyText"
                    class="form-control shadow-none"
                    rows="3"
                    placeholder="Type official response..."
                    required
                ></textarea>

            </div>

            <div class="modal-footer border-top py-2 px-3">

                <button
                    type="button"
                    class="btn btn-light border btn-compact px-3"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-primary btn-compact px-3 shadow-sm"
                    id="btnSendAdminReply"
                    onclick="submitAdminReply()"
                >
                    <i class="fas fa-paper-plane me-1"></i>
                    <span>Send Reply</span>
                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     2. DELETE CONFIRMATION MODAL
========================================================= --}}

<div class="modal fade" id="adminDeleteModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg overflow-hidden text-center p-3">

            <div class="modal-body p-1">

                <div
                    class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                    style="width: 50px; height: 50px;"
                >
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 14.5px;">
                    Delete Comment?
                </h6>

                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.5;">
                    This comment and all nested replies will be permanently deleted.
                </p>

                <div class="d-flex justify-content-center gap-2">

                    <button
                        type="button"
                        class="btn btn-light border btn-compact px-3"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <form
                        id="adminDeleteForm"
                        method="POST"
                        action=""
                        class="m-0"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="btn btn-danger btn-compact px-3 shadow-sm"
                        >
                            <i class="fas fa-trash-alt me-1"></i>
                            Delete
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     3. SYSTEM NOTIFICATION MODAL
========================================================= --}}

<div
    class="modal fade"
    id="systemNoticeModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index: 106500;"
>

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div
                    id="systemNoticeIconWrap"
                    class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                    style="width: 50px; height: 50px;"
                >
                    <i id="systemNoticeIcon" class="fas fa-check-circle fs-4"></i>
                </div>

                <h6
                    class="fw-bold text-dark mb-2"
                    id="systemNoticeTitle"
                    style="font-size: 14.5px;"
                >
                    Notice
                </h6>

                <p
                    class="text-muted small mb-3"
                    id="systemNoticeMsg"
                    style="font-size: 12px; line-height: 1.5;"
                ></p>

                <button
                    type="button"
                    class="btn btn-dark btn-compact px-4"
                    data-bs-dismiss="modal"
                >
                    OK
                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     JAVASCRIPT — EXISTING FUNCTIONALITY PRESERVED
========================================================= --}}

<script>
    let filterDebounceTimer = null;

    function showSystemNotice(title, message, isError = false) {
        document.getElementById('systemNoticeTitle').innerText = title;
        document.getElementById('systemNoticeMsg').innerText = message;

        const wrap = document.getElementById('systemNoticeIconWrap');
        const icon = document.getElementById('systemNoticeIcon');

        if (wrap && icon) {
            wrap.className = isError
                ? 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3'
                : 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3';

            icon.className = isError
                ? 'fas fa-exclamation-circle fs-4'
                : 'fas fa-check-circle fs-4';
        }

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('systemNoticeModal')
        ).show();
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
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
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

                pendingBadge.innerHTML = `
                    <span class="pulse-dot"></span>
                    <span class="bn-digit me-1">${data.pending_count}</span>
                    <span>Pending Approval</span>
                `;
            } else {
                pendingBadge.className = 'pending-alert-badge bg-light text-muted border';

                pendingBadge.innerHTML = `
                    <i class="fas fa-check-circle text-success me-1"></i>
                    <span>All caught up</span>
                `;
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

            body: JSON.stringify({
                auto_approve: isChecked
            })
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

        document.getElementById('replyToCommentText').innerText =
            '"' + commentText + '"';

        document.getElementById('adminReplyText').value = '';

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('instantReplyModal')
        ).show();
    }


    function submitAdminReply() {
        const id = document.getElementById('replyTargetCommentId').value;

        const reply = document.getElementById('adminReplyText').value.trim();

        const btn = document.getElementById('btnSendAdminReply');

        if (!reply) {
            showSystemNotice(
                'Reply Required',
                'Please enter reply text before sending.',
                true
            );

            return;
        }

        btn.disabled = true;

        btn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-1"></span>
            <span>Sending...</span>
        `;

        fetch(`{{ url('admin/content/blogs/comment') }}/${id}/reply`, {
            method: 'POST',

            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },

            body: JSON.stringify({
                reply: reply
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;

            btn.innerHTML = `
                <i class="fas fa-paper-plane me-1"></i>
                <span>Send Reply</span>
            `;

            bootstrap.Modal.getInstance(
                document.getElementById('instantReplyModal')
            )?.hide();

            showSystemNotice('Replied', data.message, false);

            loadCommentsAjax(1);
        })
        .catch(() => {
            btn.disabled = false;

            btn.innerHTML = `
                <i class="fas fa-paper-plane me-1"></i>
                <span>Send Reply</span>
            `;

            showSystemNotice(
                'Error',
                'Failed to send reply. Please try again.',
                true
            );
        });
    }


    // Delete Confirmation Modal

    function openDeleteModal(actionUrl) {
        document.getElementById('adminDeleteForm').action = actionUrl;

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('adminDeleteModal')
        ).show();
    }
</script>

@endsection