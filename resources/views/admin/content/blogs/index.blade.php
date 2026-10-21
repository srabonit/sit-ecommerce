@extends('layouts.admin')

@section('title', 'Blog Posts Manager - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #blogManagerRoot .btn,
    #blogManagerRoot .btn-sm,
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

    /* Compact & Sleek Button Sizing */
    #blogManagerRoot .btn-compact,
    #deleteBlogModal .btn-compact {
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

    .action-sq-btn {
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        transition: all 0.15s ease;
    }

    .blog-action-group {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
    }

    /* Enterprise Cards */
    .blog-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Filter Controls */
    .blog-filter-input,
    .blog-filter-select {
        font-size: 12px !important;
        height: 34px !important;
        padding: 5px 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .blog-filter-input:focus,
    .blog-filter-select:focus {
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
    .custom-blog-table th {
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
    .custom-blog-table td {
        padding: 10px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-blog-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .blog-row-title {
        font-size: 13px !important;
        line-height: 1.35 !important;
        font-weight: 700;
        color: #0f172a;
    }

    /* Soft Enterprise Badges */
    .badge-soft-published { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-soft-draft     { background-color: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }

    /* Pagination Styling */
    .blog-pagination-wrapper nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px;
        width: 100% !important;
    }
    @media (min-width: 768px) {
        .blog-pagination-wrapper nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }
    .blog-pagination-wrapper nav svg {
        width: 14px !important;
        height: 14px !important;
    }
    .blog-pagination-wrapper .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 4px;
        padding-left: 0;
        list-style: none;
    }
    .blog-pagination-wrapper .page-item .page-link,
    .blog-pagination-wrapper .page-item span {
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
    .blog-pagination-wrapper .page-item.active .page-link,
    .blog-pagination-wrapper .page-item.active span {
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

    /* Mobile 100/100 Optimizations */
    @media (max-width: 767.98px) {
        .blog-index-header h5 {
            font-size: 16px !important;
        }
        .blog-header-btn-wrap {
            width: 100%;
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }
        .blog-header-btn-wrap .btn-compact {
            flex: 1 1 0;
            padding: 7px 10px !important;
        }
        .custom-blog-table th,
        .custom-blog-table td {
            padding: 9px 10px !important;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="blogManagerRoot">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 blog-index-header">
        <div class="w-100 w-sm-auto">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">Blog Posts</h5>
                <div class="blog-header-btn-wrap d-inline-flex gap-2">
                    <a href="{{ route('admin.content.blogs.create') }}" class="btn btn-outline-primary btn-compact shadow-xs">
                        <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                        <span>Add Post</span>
                    </a>
                    <a href="{{ route('admin.content.blogs.comments') }}" class="btn btn-primary btn-compact shadow-xs">
                        <i class="fas fa-comments me-1" style="font-size: 10px;"></i>
                        <span>Manage Comments</span>
                    </a>
                </div>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Manage articles, categories, comments status, and real-time filters.</small>
        </div>
        <div class="text-muted small bg-white border px-2 py-1 flex-shrink-0" style="font-size: 11.5px; border-radius: 5px;">
            Total Posts: <strong class="text-dark font-monospace bn-digit" id="totalPostsCount">{{ isset($posts) ?$posts->total() : 0 }}</strong>
        </div>
    </div>

    <!-- Live Search & Multi-Filters Toolbar -->
    <div class="blog-panel-card p-3 mb-3">
        <div class="row g-2 align-items-center">
            <!-- 1. Search Bar -->
            <div class="col-12 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text search-addon-icon py-1"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                    <input type="text" id="ajaxSearchPosts" class="form-control blog-filter-input" placeholder="Search by name, category, or author..." oninput="triggerBlogFilter()">
                </div>
            </div>

            <!-- 2. Category Filter -->
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="categoryFilter" class="form-select blog-filter-select" onchange="triggerBlogFilter()">
                    <option value="all">Category: All</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as$cat): ?>
                            <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 3. Status Filter -->
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="statusFilter" class="form-select blog-filter-select" onchange="triggerBlogFilter()">
                    <option value="all">Status: All</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <!-- 4. Comments Toggle Filter -->
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="commentsFilter" class="form-select blog-filter-select" onchange="triggerBlogFilter()">
                    <option value="all">Comments: All</option>
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>

            <!-- 5. Sort Order -->
            <div class="col-6 col-sm-3 col-lg-2">
                <select id="sortFilter" class="form-select blog-filter-select" onchange="triggerBlogFilter()">
                    <option value="recent">Recent First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="title_asc">Title (A - Z)</option>
                    <option value="title_desc">Title (Z - A)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="blog-panel-card overflow-hidden position-relative">
        <div id="postsLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 5;">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>

        <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
            <table class="table custom-blog-table align-middle mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4">Title &amp; Author</th>
                        <th>Category</th>
                        <th class="text-center">Comments</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="min-width: 100px;">Date</th>
                        <th class="text-end pe-3 pe-md-4" style="min-width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody id="postsTbody">
                    <?php if (isset($posts) && count($posts) > 0): ?>
                        <?php foreach ($posts as$post): ?>
                            <tr id="blog-row-<?php echo e($post->id); ?>">
                                <td class="ps-3 ps-md-4" style="min-width: 200px; max-width: 320px;">
                                    <strong class="d-block mb-1 text-wrap blog-row-title"><?php echo e($post->title); ?></strong>
                                    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 11px;">
                                        <span><i class="fas fa-user-edit me-1 text-primary"></i><?php echo e($post->author_name ?? 'Editorial Desk'); ?></span>
                                        <span>&bull;</span>
                                        <a href="<?php echo e(route('blogs.show', $post->slug)); ?>" target="_blank" class="text-decoration-none text-primary fw-semibold">
                                            <i class="fas fa-external-link-alt me-1" style="font-size: 9.5px;"></i><span>View</span>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border px-2 py-1 fw-semibold" style="font-size: 10.5px;"><?php echo e($post->category ?? 'General'); ?></span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-compact <?php echo !empty($post->enable_comments) ? 'btn-outline-success' : 'btn-outline-secondary'; ?>" style="font-size: 10.5px !important; padding: 3px 8px !important;" onclick="togglePostComments(<?php echo e($post->id); ?>, this)">
                                        <i class="fas <?php echo !empty($post->enable_comments) ? 'fa-comment-dots' : 'fa-comment-slash'; ?> me-1"></i>
                                        <span><?php echo !empty($post->enable_comments) ? 'Enabled' : 'Disabled'; ?></span>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo ($post->status ?? 'published') === 'published' ? 'badge-soft-published' : 'badge-soft-draft'; ?> px-2 py-1 text-uppercase fw-bold" style="font-size: 9.5px; letter-spacing: 0.03em;">
                                        <?php echo e($post->status ?? 'published'); ?>
                                    </span>
                                </td>
                                <td class="text-end text-muted font-monospace bn-digit" style="font-size: 11.5px; white-space: nowrap;">
                                    <span title="<?php echo !empty($post->updated_at) ? 'Updated: ' . date('d M, Y', strtotime($post->updated_at)) : ''; ?>">
                                        <i class="far fa-calendar-alt me-1 text-primary"></i><?php echo e(date('d M, Y', strtotime($post->created_at))); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3 pe-md-4">
                                    <div class="blog-action-group">
                                        <a href="<?php echo e(route('admin.content.blogs.edit', $post->id)); ?>" class="btn btn-light border text-dark btn-compact shadow-xs" title="Edit Post">
                                            <i class="fas fa-edit text-primary me-1" style="font-size: 10px;"></i>
                                            <span>Edit</span>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger action-sq-btn shadow-xs" title="Delete Post" onclick="openBlogDeleteModal('<?php echo e(route('admin.content.blogs.destroy', $post->id)); ?>', '<?php echo e(addslashes($post->title)); ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="far fa-newspaper fa-2x mb-2 d-block opacity-25"></i>
                                <p class="mb-0 fw-semibold">No matching blog posts found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top bg-light bg-opacity-50 blog-pagination-wrapper overflow-auto" id="postsPaginationWrap">
            <?php if (method_exists($posts, 'hasPages') &&$posts->hasPages()): ?>
                <?php echo $posts->links('pagination::bootstrap-5'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteBlogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 8px !important;">
            <div class="modal-body p-3 text-center">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Blog Post?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Permanently delete <strong id="deleteBlogTitle" class="text-dark"></strong>? All comments will also be removed.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteBlogForm" method="POST" action="" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-compact px-3 shadow-xs">Delete</button>
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
                    btn.style.cssText = 'font-size: 10.5px !important; padding: 3px 8px !important;';
                    btn.innerHTML = '<i class="fas fa-comment-dots me-1"></i><span>Enabled</span>';
                } else {
                    btn.className = 'btn btn-compact btn-outline-secondary';
                    btn.style.cssText = 'font-size: 10.5px !important; padding: 3px 8px !important;';
                    btn.innerHTML = '<i class="fas fa-comment-slash me-1"></i><span>Disabled</span>';
                }
            }
        });
    }
</script>
@endsection