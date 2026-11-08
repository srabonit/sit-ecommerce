<?php if (isset($posts) && count($posts) > 0): ?>

    <?php foreach ($posts as $post): ?>

        <tr id="blog-row-<?php echo e($post->id); ?>" class="align-middle">

            <!-- Title & Author -->
            <td class="ps-3 ps-md-4 py-3" style="min-width: 220px; max-width: 340px;">

                <div class="blog-row-title-wrapper">

                    <strong
                        class="text-dark d-block mb-1 text-wrap blog-row-title"
                        style="font-size: 13px; line-height: 1.45; font-weight: 700;"
                    >
                        <?php echo e($post->title); ?>
                    </strong>

                    <div
                        class="d-flex align-items-center flex-wrap gap-2 text-muted"
                        style="font-size: 11px;"
                    >

                        <span class="d-inline-flex align-items-center">
                            <i class="fas fa-user-edit me-1 text-primary"></i>
                            <?php echo e($post->author_name ?? 'Editorial Desk'); ?>
                        </span>

                        <span class="text-secondary opacity-50">&bull;</span>

                        <a
                            href="<?php echo e(route('blogs.show', $post->slug)); ?>"
                            target="_blank"
                            class="text-decoration-none text-primary fw-semibold d-inline-flex align-items-center"
                            title="View Blog Post"
                        >
                            <i class="fas fa-external-link-alt me-1" style="font-size: 9px;"></i>
                            <span>View Post</span>
                        </a>

                    </div>

                </div>

            </td>


            <!-- Category -->
            <td class="py-3 text-nowrap">

                <span
                    class="badge bg-light text-primary border px-2 py-1 fw-semibold"
                    style="font-size: 10px; border-radius: 5px !important; letter-spacing: .02em;"
                >
                    <?php echo e($post->category ?? 'General'); ?>
                </span>

            </td>


            <!-- Comments Toggle -->
            <td class="text-center py-3 text-nowrap">

                <button
                    type="button"
                    class="btn btn-sm px-2 py-1 <?php echo !empty($post->enable_comments) ? 'btn-outline-success' : 'btn-outline-secondary'; ?>"
                    style="font-size: 10.5px; border-radius: 5px !important; line-height: 1.3; font-weight: 600;"
                    onclick="togglePostComments(<?php echo e($post->id); ?>, this)"
                    title="Toggle Comments"
                >

                    <i class="fas <?php echo !empty($post->enable_comments) ? 'fa-comment-dots' : 'fa-comment-slash'; ?> me-1"></i>

                    <span>
                        <?php echo !empty($post->enable_comments) ? 'Enabled' : 'Disabled'; ?>
                    </span>

                </button>

            </td>


            <!-- Publication Status -->
            <td class="text-center py-3 text-nowrap">

                <span
                    class="badge <?php echo ($post->status ?? 'published') === 'published' ? 'bg-success text-white' : 'bg-warning text-dark'; ?> px-2 py-1 text-uppercase fw-bold"
                    style="font-size: 9px; letter-spacing: .04em; border-radius: 5px !important;"
                >
                    <?php echo e($post->status ?? 'published'); ?>
                </span>

            </td>


            <!-- Created & Updated Date -->
            <td class="text-end text-muted py-3 text-nowrap font-monospace bn-digit">

                <div
                    class="d-flex align-items-center justify-content-end"
                    title="Created At"
                    style="font-size: 11px;"
                >
                    <i class="far fa-calendar-plus me-1 text-success"></i>

                    <?php echo e(date('d M, Y', strtotime($post->created_at))); ?>
                </div>

                <?php if (!empty($post->updated_at) && $post->updated_at != $post->created_at): ?>

                    <div
                        class="d-flex align-items-center justify-content-end text-secondary opacity-75 mt-1"
                        style="font-size: 10px;"
                        title="Updated At"
                    >
                        <i class="far fa-edit me-1 text-info"></i>

                        <?php echo e(date('d M, Y', strtotime($post->updated_at))); ?>
                    </div>

                <?php endif; ?>

            </td>


            <!-- Actions -->
            <td class="text-end pe-3 pe-md-4 py-3 text-nowrap">

                <div class="d-inline-flex justify-content-end align-items-center gap-2">

                    <!-- Edit -->
                    <a
                        href="<?php echo e(route('admin.content.blogs.edit', $post->id)); ?>"
                        class="btn btn-sm btn-light border text-dark px-2 py-1 shadow-xs fw-semibold d-inline-flex align-items-center justify-content-center"
                        style="font-size: 11px; border-radius: 5px !important; min-height: 29px;"
                        title="Edit Blog Post"
                    >
                        <i class="fas fa-edit me-1 text-primary" style="font-size: 10px;"></i>

                        <span>Edit</span>
                    </a>


                    <!-- Delete -->
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs"
                        style="width: 29px; height: 29px; border-radius: 5px !important;"
                        title="Delete Blog Post"
                        onclick="openBlogDeleteModal('<?php echo e(route('admin.content.blogs.destroy', $post->id)); ?>', '<?php echo e(addslashes($post->title)); ?>')"
                    >
                        <i class="fas fa-trash-alt" style="font-size: 10px;"></i>
                    </button>

                </div>

            </td>

        </tr>

    <?php endforeach; ?>

<?php else: ?>

    <!-- Empty State -->
    <tr>

        <td colspan="6" class="text-center py-5 text-muted">

            <div class="d-flex flex-column align-items-center justify-content-center">

                <div
                    class="d-flex align-items-center justify-content-center bg-light rounded-circle mb-3"
                    style="width: 54px; height: 54px;"
                >
                    <i class="far fa-newspaper text-secondary opacity-50" style="font-size: 23px;"></i>
                </div>

                <span class="fw-semibold text-dark" style="font-size: 12.5px;">
                    No matching blog posts found.
                </span>

                <small class="text-muted mt-1" style="font-size: 11px;">
                    Try adjusting your search or filter criteria.
                </small>

            </div>

        </td>

    </tr>

<?php endif; ?>