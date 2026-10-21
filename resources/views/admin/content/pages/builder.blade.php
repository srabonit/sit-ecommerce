@extends('admin.layouts.app')

@section('title', isset($page) ? 'Edit Page Builder' : 'New Page Builder')

@section('content')
<style>
    /* Global Strict 5px Element Radius */
    #pageBuilderRoot .btn,
    #pageBuilderRoot .btn-sm,
    #pageBuilderRoot button,
    #pageBuilderRoot .form-control,
    #pageBuilderRoot .form-select,
    #pageBuilderRoot .input-group-text,
    #pageBuilderRoot .dropdown-menu,
    #pageBuilderRoot .dropdown-item {
        border-radius: 5px !important;
    }

    #pageBuilderRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #pageBuilderRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Buttons */
    #pageBuilderRoot .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    #pageBuilderRoot .btn-block-ctrl {
        width: 26px !important;
        height: 26px !important;
        padding: 0 !important;
        font-size: 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.15s ease;
    }
    #pageBuilderRoot .btn-block-ctrl:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
    #pageBuilderRoot .btn-block-ctrl.btn-ctrl-danger:hover {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fca5a5;
    }

    /* Enterprise Card Surface */
    .builder-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .builder-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        padding-bottom: 10px;
        margin-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Dynamic Block Item Card */
    .block-item-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .block-item-card:hover {
        border-color: #94a3b8;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .block-item-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 8px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .empty-canvas-box {
        background: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 8px !important;
        padding: 36px 20px;
        text-align: center;
        color: #64748b;
    }

    /* Form Controls */
    #pageBuilderRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }
    #pageBuilderRoot .form-control,
    #pageBuilderRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #pageBuilderRoot .form-control:focus,
    #pageBuilderRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .builder-header h5 {
            font-size: 16px !important;
        }
        .builder-top-actions {
            width: 100%;
            display: flex;
            gap: 8px;
        }
        .builder-top-actions .btn-compact {
            flex: 1 1 0;
        }
        .add-block-dropdown-wrap {
            width: 100%;
        }
        .add-block-dropdown-wrap .btn-compact {
            width: 100%;
        }
        .add-block-dropdown-wrap .dropdown-menu {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="pageBuilderRoot">
    <form action="{{ isset($page) ? route('admin.content.pages.update', $page->id) : route('admin.content.pages.store') }}" method="POST" id="pageBuilderForm">
        @csrf
        
        <!-- Top Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 builder-header">
            <div>
                <h5 class="fw-bold text-dark mb-1" style="font-size: 18px;">{{ isset($page) ? 'Edit Page Builder' : 'Create New Page' }}</h5>
                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Assemble responsive block sections for this page</small>
            </div>
            <div class="d-flex gap-2 builder-top-actions align-self-start align-self-sm-auto">
                <a href="{{ route('admin.content.pages.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">Cancel</a>
                <button type="submit" class="btn btn-primary btn-compact shadow-xs">Save Page</button>
            </div>
        </div>

        <div class="row g-3">
            <!-- Left Side: Page Info & Block Canvas -->
            <div class="col-12 col-lg-8">
                <div class="builder-card p-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label">Page Title</label>
                        <input type="text" name="title" id="pageTitle" value="{{ old('title', $page->title ?? '') }}" class="form-control fw-semibold" placeholder=" About Us / Terms of Service" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">URL Slug</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted">/page/</span>
                            <input type="text" name="slug" id="pageSlug" value="{{ old('slug', $page->slug ?? '') }}" class="form-control font-monospace" placeholder="about-us">
                        </div>
                    </div>
                </div>

                <!-- Block Container Toolbar -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 14px;">
                        <i class="fas fa-layer-group text-primary me-2"></i>
                        <span>Page Layout Blocks</span>
                    </h6>
                    <div class="dropdown add-block-dropdown-wrap align-self-start align-self-sm-auto">
                        <button class="btn btn-primary btn-compact dropdown-toggle shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-plus me-1"></i>
                            <span>Add Section Block</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border p-1" style="min-width: 220px;">
                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('hero_banner')">
                                    <i class="fas fa-image text-primary me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Hero Banner Section</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('rich_text')">
                                    <i class="fas fa-align-left text-success me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Rich Text Content</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('features_grid')">
                                    <i class="fas fa-th-large text-warning me-2" style="width: 16px; text-align: center;"></i>
                                    <span>4-Column Feature Icons</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('products_grid')">
                                    <i class="fas fa-box text-danger me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Featured Products Grid</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('recent_posts')">
                                    <i class="fas fa-newspaper text-info me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Recent Blog Posts</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Dynamic Blocks Canvas -->
                <div id="blocksCanvas" class="d-flex flex-column gap-3 mb-3">
                    <!-- Dynamic Blocks Injected Here via JS -->
                </div>

                <input type="hidden" name="content_blocks" id="contentBlocksJson">
            </div>

            <!-- Right Side: Sidebar Meta Controls -->
            <div class="col-12 col-lg-4">
                <div class="builder-card p-3 mb-3">
                    <h6 class="builder-section-title">Publish Settings</h6>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" {{ old('is_published', $page->is_published ?? true) ? 'checked' : '' }} style="cursor: pointer;">
                        <label class="form-check-label fw-semibold small text-dark" for="is_published" style="font-size: 12px; cursor: pointer;">Publish Online</label>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Page Template</label>
                        <select name="template" class="form-select form-select-sm">
                            <option value="default" {{ ($page->template ?? '') == 'default' ? 'selected' : '' }}>Default (Contained Box)</option>
                            <option value="full-width" {{ ($page->template ?? '') == 'full-width' ? 'selected' : '' }}>Full Width Canvas</option>
                        </select>
                    </div>
                </div>

                <div class="builder-card p-3">
                    <h6 class="builder-section-title">SEO Configuration</h6>
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}" class="form-control form-control-sm">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" rows="3" class="form-control form-control-sm">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Simple Block Builder Engine (Pure Vanilla JS) -->
<script>
    let activeBlocks = @json($page->content_blocks ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        renderBlocks();

        document.getElementById('pageBuilderForm').addEventListener('submit', () => {
            saveCurrentInputs();
            document.getElementById('contentBlocksJson').value = JSON.stringify(activeBlocks);
        });
    });

    function renderBlocks() {
        const canvas = document.getElementById('blocksCanvas');
        canvas.innerHTML = '';

        if (activeBlocks.length === 0) {
            canvas.innerHTML = `
                <div class="empty-canvas-box">
                    <i class="fas fa-puzzle-piece fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0 small">No blocks added yet. Click <strong>Add Section Block</strong> above to start building.</p>
                </div>`;
            return;
        }

        activeBlocks.forEach((b, idx) => {
            let blockCard = document.createElement('div');
            blockCard.className = 'block-item-card';
            blockCard.dataset.index = idx;

            let headerTitle = b.type.replace('_', ' ').toUpperCase();
            
            blockCard.innerHTML = `
                <div class="block-item-header">
                    <span class="fw-bold small text-dark d-flex align-items-center" style="font-size: 11.5px; letter-spacing: 0.03em;">
                        <i class="fas fa-grip-vertical text-muted me-2"></i>
                        <span>${headerTitle}</span>
                    </span>
                    <div class="d-inline-flex align-items-center gap-1">
                        <button type="button" class="btn-block-ctrl" onclick="moveBlock(${idx}, -1)" title="Move Up"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="btn-block-ctrl" onclick="moveBlock(${idx}, 1)" title="Move Down"><i class="fas fa-arrow-down"></i></button>
                        <button type="button" class="btn-block-ctrl btn-ctrl-danger" onclick="removeBlock(${idx})" title="Remove Block"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="p-3">
                    ${renderBlockFields(b, idx)}
                </div>
            `;
            canvas.appendChild(blockCard);
        });
    }

    function renderBlockFields(b, idx) {
        if (b.type === 'hero_banner') {
            return `
                <div class="row g-2">
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Title" value="${b.data.title || ''}" onchange="updateData(${idx}, 'title', this.value)"></div>
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Subtitle" value="${b.data.subtitle || ''}" onchange="updateData(${idx}, 'subtitle', this.value)"></div>
                    <div class="col-12"><textarea class="form-control form-control-sm" placeholder="Description" rows="2" onchange="updateData(${idx}, 'description', this.value)">${b.data.description || ''}</textarea></div>
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Button Text" value="${b.data.btn_text || ''}" onchange="updateData(${idx}, 'btn_text', this.value)"></div>
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Button URL" value="${b.data.btn_url || ''}" onchange="updateData(${idx}, 'btn_url', this.value)"></div>
                </div>`;
        }
        if (b.type === 'rich_text') {
            return `<textarea class="form-control form-control-sm" rows="5" placeholder="HTML or Text Content" onchange="updateData(${idx}, 'content', this.value)">${b.data.content || ''}</textarea>`;
        }
        if (b.type === 'products_grid') {
            return `
                <div class="row g-2">
                    <div class="col-12 col-md-8"><input type="text" class="form-control form-control-sm" placeholder="Heading Title" value="${b.data.title || 'Featured Products'}" onchange="updateData(${idx}, 'title', this.value)"></div>
                    <div class="col-12 col-md-4"><input type="number" class="form-control form-control-sm bn-digit" placeholder="Items Limit" value="${b.data.limit || 8}" onchange="updateData(${idx}, 'limit', this.value)"></div>
                </div>`;
        }
        if (b.type === 'recent_posts') {
            return `
                <div class="row g-2">
                    <div class="col-12 col-md-8"><input type="text" class="form-control form-control-sm" placeholder="Heading Title" value="${b.data.title || 'From Our Blog'}" onchange="updateData(${idx}, 'title', this.value)"></div>
                    <div class="col-12 col-md-4"><input type="number" class="form-control form-control-sm bn-digit" placeholder="Posts Limit" value="${b.data.limit || 3}" onchange="updateData(${idx}, 'limit', this.value)"></div>
                </div>`;
        }
        if (b.type === 'features_grid') {
            return `
                <input type="text" class="form-control form-control-sm mb-2" placeholder="Section Title ( Why Choose Us)" value="${b.data.title || ''}" onchange="updateData(${idx}, 'title', this.value)">
                <small class="text-muted d-block" style="font-size: 11px;">4-Item features will be automatically displayed based on defaults.</small>
            `;
        }
        return '';
    }

    function addBlock(type) {
        saveCurrentInputs();
        let defaultData = {};
        if (type === 'features_grid') {
            defaultData = {
                title: 'Why Choose Us',
                items: [
                    { icon: 'fas fa-truck', title: 'Free Delivery', description: 'On all orders above 1500৳' },
                    { icon: 'fas fa-shield-alt', title: '100% Secure', description: 'Safe payment gateways' },
                    { icon: 'fas fa-undo', title: '7 Days Return', description: 'Instant refund guarantee' },
                    { icon: 'fas fa-headset', title: '24/7 Support', description: 'Dedicated hotline help' },
                ]
            };
        }
        activeBlocks.push({ type: type, data: defaultData });
        renderBlocks();
    }

    function removeBlock(idx) {
        saveCurrentInputs();
        activeBlocks.splice(idx, 1);
        renderBlocks();
    }

    function moveBlock(idx, dir) {
        saveCurrentInputs();
        const target = idx + dir;
        if (target >= 0 && target < activeBlocks.length) {
            const temp = activeBlocks[idx];
            activeBlocks[idx] = activeBlocks[target];
            activeBlocks[target] = temp;
            renderBlocks();
        }
    }

    function updateData(idx, key, val) {
        if (!activeBlocks[idx].data) activeBlocks[idx].data = {};
        activeBlocks[idx].data[key] = val;
    }

    function saveCurrentInputs() {
        // DOM values auto-sync via onchange
    }
</script>
@endsection