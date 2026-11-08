@extends('layouts.admin')

@section('title', 'Blog Posts Manager - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       BLOG MANAGER — KAIADMIN LITE CLASSIC / WHITE CLASSIC
    ========================================================= */

    #blogManagerRoot {
        --blog-primary: var(--bs-primary, #3b82f6);
        --blog-primary-soft: #eff6ff;
        --blog-success: #16a34a;
        --blog-success-soft: #f0fdf4;
        --blog-warning: #d97706;
        --blog-warning-soft: #fffbeb;
        --blog-danger: #dc3545;
        --blog-text: #344054;
        --blog-heading: #1f2937;
        --blog-muted: #667085;
        --blog-border: #e5e7eb;
        --blog-light: #f8fafc;
        --blog-white: #ffffff;
        color: var(--blog-text);
        font-size: 13px;
        min-width: 0;
    }

    #blogManagerRoot *,
    #deleteBlogModal * {
        box-sizing: border-box;
    }

    /* Common Radius */
    #blogManagerRoot .btn,
    #blogManagerRoot button,
    #blogManagerRoot .form-control,
    #blogManagerRoot .form-select,
    #blogManagerRoot .badge,
    #blogManagerRoot .input-group-text,
    #blogManagerRoot .page-link,
    #blogManagerRoot .page-item span,
    #deleteBlogModal .btn {
        border-radius: 5px !important;
    }

    #blogManagerRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #blogManagerRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Buttons */
    #blogManagerRoot .btn-compact,
    #deleteBlogModal .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 5px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.4 !important;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #blogManagerRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    #blogManagerRoot .action-sq-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 29px !important;
        height: 29px !important;
        min-width: 29px !important;
        padding: 0 !important;
        font-size: 11px !important;
        transition: all .18s ease;
    }

    #blogManagerRoot .blog-action-group {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
    }

    /* Panel Cards */
    #blogManagerRoot .blog-panel-card {
        background: var(--blog-white);
        border: 1px solid var(--blog-border);
        border-radius: 7px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .04);
    }

    /* Header */
    #blogManagerRoot .blog-index-header h5 {
        color: var(--blog-heading);
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.35px;
    }

    #blogManagerRoot .blog-header-description {
        color: var(--blog-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    #blogManagerRoot .blog-total-count {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border: 1px solid var(--blog-border);
        border-radius: 5px;
        background: var(--blog-white);
        color: var(--blog-muted);
        font-size: 11.5px;
        white-space: nowrap;
    }

    #blogManagerRoot .blog-total-count strong {
        color: var(--blog-heading);
        font-size: 13px;
    }

    /* Filter Toolbar */
    #blogManagerRoot .blog-filter-input,
    #blogManagerRoot .blog-filter-select {
        width: 100%;
        height: 36px !important;
        padding: 6px 10px !important;
        border: 1px solid #d0d5dd !important;
        border-radius: 5px !important;
        background-color: #fff !important;
        color: var(--blog-text) !important;
        font-size: 12px !important;
        box-shadow: none !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #blogManagerRoot .blog-filter-input::placeholder {
        color: #98a2b3;
    }

    #blogManagerRoot .blog-filter-input:focus,
    #blogManagerRoot .blog-filter-select:focus {
        border-color: var(--blog-primary) !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .10) !important;
        outline: none;
    }

    #blogManagerRoot .search-addon-icon {
        height: 36px !important;
        padding: 0 12px !important;
        border: 1px solid #d0d5dd !important;
        border-right: 0 !important;
        background: var(--blog-light) !important;
        color: #667085 !important;
    }

    /* Table */
    #blogManagerRoot .blog-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #blogManagerRoot .custom-blog-table {
        width: 100%;
        min-width: 850px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #blogManagerRoot .custom-blog-table thead th {
        padding: 12px 14px;
        background: #f8fafc;
        color: #667085;
        border-bottom: 1px solid var(--blog-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #blogManagerRoot .custom-blog-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        color: var(--blog-text);
        border-bottom: 1px solid #f0f1f3;
        font-size: 12.5px;
    }

    #blogManagerRoot .custom-blog-table tbody tr {
        transition: background-color .15s ease;
    }

    #blogManagerRoot .custom-blog-table tbody tr:hover {
        background-color: #f9fafb;
    }

    #blogManagerRoot .custom-blog-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #blogManagerRoot .blog-row-title {
        display: block;
        margin-bottom: 5px;
        color: var(--blog-heading);
        font-size: 13px !important;
        font-weight: 650;
        line-height: 1.45 !important;
        overflow-wrap: anywhere;
    }

    #blogManagerRoot .blog-author-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        color: var(--blog-muted);
        font-size: 11px;
    }

    #blogManagerRoot .blog-author-info a {
        font-weight: 600;
        text-decoration: none;
    }

    #blogManagerRoot .blog-author-info a:hover {
        text-decoration: underline;
    }

    /* Category Badge */
    #blogManagerRoot .blog-category-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px !important;
        border: 1px solid #dbeafe !important;
        border-radius: 5px !important;
        background: var(--blog-primary-soft) !important;
        color: #2563eb !important;
        font-size: 10.5px !important;
        font-weight: 600 !important;
        white-space: nowrap;
    }

    /* Status Badges */
    #blogManagerRoot .badge-soft-published {
        background: var(--blog-success-soft);
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    #blogManagerRoot .badge-soft-draft {
        background: var(--blog-warning-soft);
        color: #a16207;
        border: 1px solid #fde68a;
    }

    #blogManagerRoot .blog-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 75px;
        padding: 5px 8px !important;
        border-radius: 5px !important;
        font-size: 9.5px !important;
        font-weight: 700 !important;
        letter-spacing: .035em;
    }

    /* Date */
    #blogManagerRoot .blog-date {
        color: var(--blog-muted);
        font-size: 11.5px;
        white-space: nowrap;
    }

    #blogManagerRoot .bn-digit,
    #blogManagerRoot .font-monospace {
        font-weight: 700 !important;
    }

    /* Empty State */
    #blogManagerRoot .blog-empty-state {
        padding: 45px 15px !important;
        color: var(--blog-muted);
    }

    #blogManagerRoot .blog-empty-state i {
        color: #cbd5e1;
    }

    #blogManagerRoot .blog-empty-state p {
        margin: 0;
        color: #667085;
        font-size: 12.5px;
        font-weight: 600;
    }

    /* Loading Overlay */
    #blogManagerRoot #postsLoadingOverlay {
        z-index: 5;
        background: rgba(255, 255, 255, .78);
        backdrop-filter: blur(1px);
    }

    /* Pagination */
    #blogManagerRoot .blog-pagination-wrapper {
        padding: 13px 16px !important;
        border-top: 1px solid var(--blog-border);
        background: #fff;
        overflow-x: auto;
    }

    #blogManagerRoot .blog-pagination-wrapper nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 9px;
        width: 100% !important;
    }

    #blogManagerRoot .blog-pagination-wrapper nav > div {
        max-width: 100%;
    }

    #blogManagerRoot .blog-pagination-wrapper nav svg {
        width: 14px !important;
        height: 14px !important;
    }

    #blogManagerRoot .blog-pagination-wrapper .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 4px;
        margin: 0 !important;
        padding-left: 0;
        list-style: none;
    }

    #blogManagerRoot .blog-pagination-wrapper .page-item .page-link,
    #blogManagerRoot .blog-pagination-wrapper .page-item span {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 30px;
        height: 30px;
        padding: 3px 8px;
        border: 1px solid var(--blog-border);
        border-radius: 5px !important;
        background: #fff;
        color: #475467;
        font-size: 11.5px;
        text-decoration: none;
    }

    #blogManagerRoot .blog-pagination-wrapper .page-item.active .page-link,
    #blogManagerRoot .blog-pagination-wrapper .page-item.active span {
        border-color: var(--blog-primary) !important;
        background: var(--blog-primary) !important;
        color: #fff !important;
        font-weight: 700;
    }

    #blogManagerRoot .blog-pagination-wrapper .page-item.disabled .page-link {
        color: #c0c5ce;
        background: #f9fafb;
    }

    /* Delete Modal */
    #deleteBlogModal .modal-content {
        border: 1px solid var(--blog-border) !important;
        border-radius: 8px !important;
        box-shadow: 0 12px 35px rgba(16, 24, 40, .16);
    }

    #deleteBlogModal .modal-body {
        padding: 22px 20px !important;
    }

    #deleteBlogModal .delete-icon-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin-bottom: 12px;
        border-radius: 50%;
        background: #fef2f2;
        color: #dc2626;
    }

    #deleteBlogModal .delete-modal-title {
        color: var(--blog-heading);
        font-size: 15px;
        font-weight: 700;
    }

    #deleteBlogModal .delete-modal-description {
        color: var(--blog-muted);
        font-size: 12px;
        line-height: 1.55;
    }

    /* Responsive */
    @media (min-width: 768px) {
        #blogManagerRoot .blog-pagination-wrapper nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }

    @media (max-width: 767.98px) {
        #blogManagerRoot {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #blogManagerRoot .blog-index-header {
            align-items: stretch !important;
        }

        #blogManagerRoot .blog-index-header h5 {
            font-size: 16px !important;
        }

        #blogManagerRoot .blog-header-content {
            width: 100%;
        }

        #blogManagerRoot .blog-header-btn-wrap {
            display: flex !important;
            width: 100%;
            gap: 7px !important;
            margin-top: 9px;
        }

        #blogManagerRoot .blog-header-btn-wrap .btn-compact {
            flex: 1 1 0;
            min-height: 34px;
            padding: 7px 8px !important;
            font-size: 11px !important;
        }

        #blogManagerRoot .blog-total-count {
            align-self: flex-start;
        }

        #blogManagerRoot .blog-panel-card {
            border-radius: 6px;
        }

        #blogManagerRoot .custom-blog-table th,
        #blogManagerRoot .custom-blog-table td {
            padding: 10px !important;
            font-size: 12px;
        }

        #blogManagerRoot .blog-pagination-wrapper {
            padding: 12px 9px !important;
        }

        #deleteBlogModal .modal-dialog {
            margin: 12px;
        }
    }

    @media (max-width: 380px) {
        #blogManagerRoot .blog-header-btn-wrap {
            flex-direction: column;
        }

        #blogManagerRoot .blog-header-btn-wrap .btn-compact {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="blogManagerRoot">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3 blog-index-header">

        <div class="blog-header-content">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                <h5 class="fw-bold mb-0">Blog Posts</h5>

                <div class="blog-header-btn-wrap d-inline-flex gap-2">
                    <a href="{{ route('admin.content.blogs.create') }}"
                       class="btn btn-outline-primary btn-compact">
                        <i class="fas fa-plus"></i>
                        <span>Add Post</span>
                    </a>

                    <a href="{{ route('admin.content.blogs.comments') }}"
                       class="btn btn-primary btn-compact">
                        <i class="fas fa-comments"></i>
                        <span>Manage Comments</span>
                    </a>
                </div>
            </div>

            <small class="blog-header-description d-block mt-2">
                Manage articles, categories, comment settings, and post visibility.
            </small>
        </div>

        <div class="blog-total-count flex-shrink-0">
            <i class="fas fa-newspaper text-primary"></i>
            <span>Total Posts</span>
            <strong class="font-monospace bn-digit" id="totalPostsCount">{{ isset($posts) ? $posts->total() : 0 }}</strong>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="blog-panel-card p-3 mb-3">
        <div class="row g-2 align-items-center">

            {{-- Search --}}
            <div class="col-12 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text search-addon-icon">
                        <i class="fas fa-search" style="font-size: 11px;"></i>
                    </span>

                    <input type="text"
                           id="ajaxSearchPosts"
                           class="form-control blog-filter-input"
                           placeholder="Search by name, category, or author..."
                           oninput="triggerBlogFilter()">
                </div>
            </div>

            {{-- Category Filter --}}
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="categoryFilter"
                        class="form-select blog-filter-select"
                        onchange="triggerBlogFilter()">
                    <option value="all">Category: All</option>

                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="statusFilter"
                        class="form-select blog-filter-select"
                        onchange="triggerBlogFilter()">
                    <option value="all">Status: All</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            {{-- Comments Filter --}}
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="commentsFilter"
                        class="form-select blog-filter-select"
                        onchange="triggerBlogFilter()">
                    <option value="all">Comments: All</option>
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>

            {{-- Sort --}}
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="sortFilter"
                        class="form-select blog-filter-select"
                        onchange="triggerBlogFilter()">
                    <option value="recent">Recent First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="title_asc">Title (A - Z)</option>
                    <option value="title_desc">Title (Z - A)</option>
                </select>
            </div>

        </div>
    </div>

    {{-- Blog Posts Table --}}
    <div class="blog-panel-card overflow-hidden position-relative">

        {{-- AJAX Loading Overlay --}}
        <div id="postsLoadingOverlay"
             class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>

        <div class="blog-table-responsive table-responsive">
            <table class="table custom-blog-table align-middle mb-0 text-nowrap">

                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4">Title &amp; Author</th>
                        <th>Category</th>
                        <th class="text-center">Comments</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="min-width: 110px;">Date</th>
                        <th class="text-end pe-3 pe-md-4" style="min-width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody id="postsTbody">
                    <?php if (isset($posts) && count($posts) > 0): ?>

                        <?php foreach ($posts as $post): ?>
                            <tr id="blog-row-<?php echo e($post->id); ?>">

                                {{-- Title & Author --}}
                                <td class="ps-3 ps-md-4" style="min-width: 220px; max-width: 340px;">

                                    <strong class="blog-row-title text-wrap">
                                        <?php echo e($post->title); ?>
                                    </strong>

                                    <div class="blog-author-info">
                                        <span>
                                            <i class="fas fa-user-edit me-1 text-primary"></i>
                                            <?php echo e($post->author_name ?? 'Editorial Desk'); ?>
                                        </span>

                                        <span class="text-secondary">&bull;</span>

                                        <a href="<?php echo e(route('blogs.show', $post->slug)); ?>"
                                           target="_blank"
                                           class="text-primary">
                                            <i class="fas fa-external-link-alt me-1" style="font-size: 9.5px;"></i>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </td>

                                {{-- Category --}}
                                <td>
                                    <span class="badge blog-category-badge">
                                        <?php echo e($post->category ?? 'General'); ?>
                                    </span>
                                </td>

                                {{-- Comments --}}
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-compact <?php echo !empty($post->enable_comments) ? 'btn-outline-success' : 'btn-outline-secondary'; ?>"
                                            style="font-size: 10.5px !important; padding: 4px 9px !important;"
                                            onclick="togglePostComments(<?php echo e($post->id); ?>, this)">

                                        <i class="fas <?php echo !empty($post->enable_comments) ? 'fa-comment-dots' : 'fa-comment-slash'; ?> me-1"></i>

                                        <span>
                                            <?php echo !empty($post->enable_comments) ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </button>
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    <span class="badge blog-status-badge <?php echo ($post->status ?? 'published') === 'published' ? 'badge-soft-published' : 'badge-soft-draft'; ?> text-uppercase">
                                        <?php echo e($post->status ?? 'published'); ?>
                                    </span>
                                </td>

                                {{-- Date --}}
                                <td class="text-end blog-date font-monospace bn-digit">
                                    <span title="<?php echo !empty($post->updated_at) ? 'Updated: ' . date('d M, Y', strtotime($post->updated_at)) : ''; ?>">
                                        <i class="far fa-calendar-alt me-1 text-primary"></i>
                                        <?php echo e(date('d M, Y', strtotime($post->created_at))); ?>
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="text-end pe-3 pe-md-4">
                                    <div class="blog-action-group">

                                        <a href="<?php echo e(route('admin.content.blogs.edit', $post->id)); ?>"
                                           class="btn btn-light border text-dark btn-compact"
                                           title="Edit Post">
                                            <i class="fas fa-edit text-primary"></i>
                                            <span>Edit</span>
                                        </a>

                                        <button type="button"
                                                class="btn btn-outline-danger action-sq-btn"
                                                title="Delete Post"
                                                onclick="openBlogDeleteModal('<?php echo e(route('admin.content.blogs.destroy', $post->id)); ?>', '<?php echo e(addslashes($post->title)); ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>

                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="text-center blog-empty-state">
                                <i class="far fa-newspaper fa-2x mb-3 d-block"></i>
                                <p>No matching blog posts found.</p>
                            </td>
                        </tr>

                    <?php endif; ?>
                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        <div class="blog-pagination-wrapper overflow-auto" id="postsPaginationWrap">
            <?php if (method_exists($posts, 'hasPages') && $posts->hasPages()): ?>
                <?php echo $posts->links('pagination::bootstrap-5'); ?>
            <?php endif; ?>
        </div>

    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteBlogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg overflow-hidden">

            <div class="modal-body text-center">

                <div class="delete-icon-wrap">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>

                <h6 class="delete-modal-title mb-2">
                    Delete Blog Post?
                </h6>

                <p class="delete-modal-description mb-3">
                    Permanently delete
                    <strong id="deleteBlogTitle" class="text-dark"></strong>?
                    All comments will also be removed.
                </p>

                <div class="d-flex justify-content-center gap-2">

                    <button type="button"
                            class="btn btn-light border btn-compact px-3"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <form id="deleteBlogForm"
                          method="POST"
                          action=""
                          class="m-0">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="btn btn-danger btn-compact px-3">
                            <i class="fas fa-trash-alt me-1"></i>
                            Delete
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let blogDebounceTimer = null;

    function triggerBlogFilter() {
        clearTimeout(blogDebounceTimer);

        blogDebounceTimer = setTimeout(() => {
            loadPostsAjax(1);
        }, 300);
    }

    function loadPostsAjax(page = 1) {
        const q = document.getElementById('ajaxSearchPosts').value;
        const category = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value;
        const comments = document.getElementById('commentsFilter').value;
        const sort = document.getElementById('sortFilter').value;
        const overlay = document.getElementById('postsLoadingOverlay');

        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');

        fetch(`{{ route('admin.content.blogs.index') }}?q=${encodeURIComponent(q)}&category=${category}&status=${status}&comments=${comments}&sort=${sort}&page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            overlay.classList.add('d-none');
            overlay.classList.remove('d-flex');

            document.getElementById('postsTbody').innerHTML = data.html;
            document.getElementById('postsPaginationWrap').innerHTML = data.pagination;
            document.getElementById('totalPostsCount').innerText = data.total;
        })
        .catch(() => {
            overlay.classList.add('d-none');
            overlay.classList.remove('d-flex');
        });
    }

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#postsPaginationWrap .page-link');

        if (pageLink) {
            const href = pageLink.getAttribute('href');

            if (href && href !== '#' && !href.startsWith('javascript:')) {
                e.preventDefault();

                const urlObj = new URL(href, window.location.origin);
                const page = urlObj.searchParams.get('page') || 1;

                loadPostsAjax(page);
            }
        }
    });

    function openBlogDeleteModal(actionUrl, blogTitle) {
        document.getElementById('deleteBlogForm').action = actionUrl;
        document.getElementById('deleteBlogTitle').innerText = '"' + blogTitle + '"';

        new bootstrap.Modal(document.getElementById('deleteBlogModal')).show();
    }

    function togglePostComments(postId, btn) {
        fetch("{{ url('admin/content/blogs') }}/" + postId + "/toggle-comments", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.enabled) {
                    btn.className = 'btn btn-compact btn-outline-success';
                    btn.style.cssText = 'font-size: 10.5px !important; padding: 4px 9px !important;';
                    btn.innerHTML = '<i class="fas fa-comment-dots me-1"></i><span>Enabled</span>';
                } else {
                    btn.className = 'btn btn-compact btn-outline-secondary';
                    btn.style.cssText = 'font-size: 10.5px !important; padding: 4px 9px !important;';
                    btn.innerHTML = '<i class="fas fa-comment-slash me-1"></i><span>Disabled</span>';
                }
            }
        });
    }
</script>
@endsection