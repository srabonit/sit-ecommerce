<?php if (isset($posts) && count($posts) > 0): ?>
    <?php foreach ($posts as $post): ?>
        <tr id="blog-row-<?php echo e($post->id); ?>" class="align-middle">
            <!-- Title & Author -->
            <td class="ps-3 ps-md-4 py-2" style="min-width: 200px; max-width: 320px;">
                <strong class="text-dark d-block mb-1 text-wrap blog-row-title" style="font-size: 13px; line-height: 1.35;"><?php echo e($post->title); ?></strong>
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 11px;">
                    <span><i class="fas fa-user-edit me-1 text-primary"></i><?php echo e($post->author_name ?? 'Editorial Desk'); ?></span>
                    <span>&bull;</span>
                    <a href="<?php echo e(route('blogs.show', $post->slug)); ?>" target="_blank" class="text-decoration-none text-primary fw-semibold d-inline-flex align-items-center">
                        <i class="fas fa-external-link-alt me-1" style="font-size: 9px;"></i><span>View</span>
                    </a>
                </div>
            </td>

            <!-- Category -->
            <td class="py-2 text-nowrap">
                <span class="badge bg-light text-primary border px-2 py-1 fw-semibold" style="font-size: 10px; border-radius: 5px !important;"><?php echo e($post->category ?? 'General'); ?></span>
            </td>

            <!-- Comments Toggle -->
            <td class="text-center py-2 text-nowrap">
                <button type="button" class="btn btn-sm px-2 py-1 <?php echo !empty($post->enable_comments) ? 'btn-outline-success' : 'btn-outline-secondary'; ?>" style="font-size: 10px; border-radius: 5px !important; line-height: 1.2;" onclick="togglePostComments(<?php echo e($post->id); ?>, this)">
                    <i class="fas <?php echo !empty($post->enable_comments) ? 'fa-comment-dots' : 'fa-comment-slash'; ?> me-1"></i>
                    <span><?php echo !empty($post->enable_comments) ? 'Enabled' : 'Disabled'; ?></span>
                </button>
            </td>

            <!-- Status -->
            <td class="text-center py-2 text-nowrap">
                <span class="badge <?php echo ($post->status ?? 'published') === 'published' ? 'bg-success text-white' : 'bg-warning text-dark'; ?> px-2 py-1 text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.03em; border-radius: 5px !important;">
                    <?php echo e($post->status ?? 'published'); ?>
                </span>
            </td>

            <!-- Date -->
            <td class="text-end text-muted py-2 text-nowrap font-monospace bn-digit" style="font-size: 11px;">
                <div title="Created At"><i class="far fa-calendar-plus me-1 text-success"></i><?php echo e(date('d M, Y', strtotime($post->created_at))); ?></div>
                <?php if (!empty($post->updated_at) && $post->updated_at != $post->created_at): ?>
                    <div class="text-secondary opacity-75 mt-1" style="font-size: 10px;" title="Updated At"><i class="far fa-edit me-1 text-info"></i><?php echo e(date('d M, Y', strtotime($post->updated_at))); ?></div>
                <?php endif; ?>
            </td>

            <!-- Actions (Compact Buttons with Strict 5px Radius) -->
            <td class="text-end pe-3 pe-md-4 py-2 text-nowrap">
                <div class="d-inline-flex justify-content-end align-items-center gap-1">
                    <a href="<?php echo e(route('admin.content.blogs.edit', $post->id)); ?>" class="btn btn-sm btn-light border text-dark px-2 py-1 shadow-xs fw-semibold d-inline-flex align-items-center" style="font-size: 11px; border-radius: 5px !important;" title="Edit">
                        <i class="fas fa-edit me-1 text-primary" style="font-size: 10px;"></i>
                        <span>Edit</span>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 26px; height: 26px; border-radius: 5px !important;" title="Delete" onclick="openBlogDeleteModal('<?php echo e(route('admin.content.blogs.destroy', $post->id)); ?>', '<?php echo e(addslashes($post->title)); ?>')">
                        <i class="fas fa-trash-alt" style="font-size: 10px;"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="text-center py-4 text-muted small">
            <i class="far fa-newspaper fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No matching blog posts found.</span>
        </td>
    </tr>
<?php endif; ?>