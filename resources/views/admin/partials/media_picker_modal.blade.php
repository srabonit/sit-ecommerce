<!-- GLOBAL WORDPRESS-STYLE MEDIA PICKER MODAL -->
<div class="modal fade" id="globalMediaPickerModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable my-2 my-md-3">
        <div class="modal-content border-0 shadow-lg overflow-hidden picker-modal-canvas">
            
            <!-- Modal Header with App-Style Segmented Tabs -->
            <div class="modal-header bg-light border-bottom py-2 px-3 d-flex justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center text-truncate" style="font-size: 13.5px;">
                        <i class="fas fa-photo-video text-primary me-2"></i>
                        <span>Select or Upload Media</span>
                    </h6>
                    <ul class="nav picker-segmented-tabs ms-auto ms-sm-2" id="mediaPickerTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active btn-compact" id="tab-library-btn" data-bs-toggle="pill" data-bs-target="#tab-library" type="button">
                                <i class="fas fa-images me-1" style="font-size: 10px;"></i>
                                <span>Media Library</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn-compact" id="tab-upload-btn" data-bs-toggle="pill" data-bs-target="#tab-upload" type="button">
                                <i class="fas fa-cloud-upload-alt me-1" style="font-size: 10px;"></i>
                                <span>Upload Files</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn-close btn-sm shadow-none flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body (Fixed Viewport Canvas) -->
            <div class="modal-body p-0 position-relative picker-modal-viewport">
                <div class="tab-content h-100" id="mediaPickerTabContent">
                    
                    <!-- 1. MEDIA LIBRARY TAB -->
                    <div class="tab-pane fade show active h-100 d-flex flex-column" id="tab-library" role="tabpanel">
                        <!-- Top Filter & Search Bar -->
                        <div class="p-2 p-md-2.5 bg-white border-bottom flex-shrink-0">
                            <div class="row g-2 align-items-center">
                                <div class="col-5 col-sm-4 col-md-3">
                                    <select id="pickerFilterType" class="form-select form-select-sm picker-filter-select" onchange="fetchPickerMedia(1)">
                                        <option value="all">All media items</option>
                                        <option value="image">Images</option>
                                        <option value="pdf">PDF Documents</option>
                                    </select>
                                </div>
                                <div class="col-7 col-sm-8 col-md-5 ms-auto">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0 picker-search-addon"><i class="fas fa-search text-muted" style="font-size: 10px;"></i></span>
                                        <input type="text" id="pickerSearchInput" class="form-control border-start-0 picker-filter-input" placeholder="Search media..." oninput="debouncePickerSearch()">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grid + Details Split Canvas -->
                        <div class="d-flex flex-column flex-md-row flex-grow-1 overflow-hidden position-relative h-100">
                            
                            <!-- Left: Media Grid Container (Scrollable) -->
                            <div class="picker-grid-column flex-grow-1 p-2 p-md-3 overflow-auto">
                                <div id="pickerMediaLoader" class="text-center py-5 d-none">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <div class="small text-muted mt-2 fw-semibold" style="font-size: 11px;">Loading media library...</div>
                                </div>

                                <div class="picker-media-grid" id="pickerGridWrapper"></div>

                                <div id="pickerEmptyNotice" class="text-center py-5 text-muted d-none">
                                    <i class="far fa-images fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                                    <p class="small mb-1 fw-semibold" style="font-size: 12px;">No media files found.</p>
                                    <button type="button" class="btn btn-outline-primary btn-compact" onclick="document.getElementById('tab-upload-btn')?.click()">
                                        <i class="fas fa-cloud-upload-alt me-1"></i> Upload Your First File
                                    </button>
                                </div>
                                
                                <!-- Load More Pagination -->
                                <div class="text-center mt-3 mb-2" id="pickerLoadMoreWrap" style="display: none;">
                                    <button type="button" class="btn btn-outline-primary btn-compact shadow-xs px-3" onclick="loadMorePickerMedia()">
                                        <i class="fas fa-spinner me-1" style="font-size: 10px;"></i>
                                        <span>Load More</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Right: Attachment Details Sidebar (Scrollable) -->
                            <div class="picker-details-column border-start bg-light bg-opacity-25 overflow-auto p-3" id="pickerSidebarColumn">
                                <div id="pickerDetailsPanel" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <span class="fw-bold text-dark text-uppercase small" style="font-size: 11px; letter-spacing: 0.04em;">Attachment Details</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle bn-digit" id="pickerSelectedBadge" style="font-size: 9.5px; border-radius: 5px;">Selected</span>
                                    </div>

                                    <div class="text-center mb-2 bg-white p-2 border" style="border-radius: 5px;">
                                        <img id="pickerDetailImg" src="" class="img-fluid" style="max-height: 120px; object-fit: contain; border-radius: 4px;">
                                    </div>

                                    <div class="small text-muted mb-3 bg-white p-2 border font-monospace bn-digit" style="font-size: 10.5px; line-height: 1.4; border-radius: 5px;">
                                        <div class="text-truncate"><strong class="text-dark" id="pickerDetailName"></strong></div>
                                        <div id="pickerDetailDate" class="text-muted"></div>
                                        <div id="pickerDetailDims" class="text-muted"></div>
                                        <div id="pickerDetailSize" class="text-muted"></div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="form-label text-muted small fw-bold mb-1" style="font-size: 11px;">Alt Text</label>
                                        <input type="text" id="pickerDetailAlt" class="form-control form-control-sm picker-filter-input" placeholder="Alternative text">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold mb-1" style="font-size: 11px;">Title</label>
                                        <input type="text" id="pickerDetailTitle" class="form-control form-control-sm picker-filter-input" placeholder="Media title">
                                    </div>

                                    <button type="button" class="btn btn-outline-secondary btn-compact w-100 shadow-xs" onclick="savePickerMetaAjax()">
                                        <i class="fas fa-check me-1" style="font-size: 10px;"></i>
                                        <span>Save Changes</span>
                                    </button>
                                </div>

                                <div id="pickerNoSelectionPanel" class="text-center py-5 text-muted">
                                    <i class="far fa-hand-pointer fa-2x mb-2 text-secondary opacity-25 d-block"></i>
                                    <p class="small mb-0" style="font-size: 11.5px; line-height: 1.4;">Click any media thumbnail to view details and select.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 2. UPLOAD FILES TAB (WordPress Drag and Drop Zone) -->
                    <div class="tab-pane fade h-100 p-3 p-md-4 overflow-auto" id="tab-upload" role="tabpanel">
                        <div class="wp-dropzone-box" id="wpPickerDropzone">
                            <div class="dropzone-icon-wrap mb-3">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 16px;">Drop files anywhere to upload</h6>
                            <p class="text-muted small mb-3" style="font-size: 12px;">or browse from your device</p>
                            
                            <label class="btn btn-primary btn-compact px-4 py-2 cursor-pointer shadow-xs mb-2">
                                <i class="fas fa-folder-open me-1.5"></i>
                                <span>Select Files</span>
                                <input type="file" id="pickerFileInput" multiple class="d-none" accept="image/*,.pdf" onchange="uploadPickerFiles(this.files)">
                            </label>

                            <div class="text-muted mt-2" style="font-size: 11px;">Maximum upload file size: 20 MB (Images, PDFs).</div>
                        </div>

                        <!-- Upload Progress Area -->
                        <div id="pickerUploadProgress" class="mt-4 px-2 px-md-4 d-none">
                            <div class="progress" style="height: 6px; border-radius: 5px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%;"></div>
                            </div>
                            <div class="text-center small text-muted mt-2 fw-semibold" style="font-size: 11px;">
                                <i class="fas fa-spinner fa-spin me-1"></i> Uploading and optimizing files, please wait...
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer (Sticky Bottom Action Bar) -->
            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <div id="pickerFooterThumbPreview" class="d-none border bg-white p-0.5 flex-shrink-0" style="border-radius: 4px; width: 28px; height: 28px; overflow: hidden;">
                        <img id="pickerFooterThumbImg" src="" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="small text-muted bn-digit text-truncate" id="pickerSelectedCountText" style="font-size: 11px;">No items selected</div>
                </div>

                <div class="d-flex gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="pickerInsertBtn" class="btn btn-primary btn-compact px-3 shadow-xs" disabled onclick="executeMediaSelection()">
                        <i class="fas fa-check me-1" style="font-size: 10px;"></i>
                        <span>Select Media</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Notice Modal -->
<div class="modal fade" id="pickerNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 5px !important;">
            <div class="modal-body p-1">
                <div id="pickerNoticeIconWrap" class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px;">
                    <i id="pickerNoticeIcon" class="fas fa-check-circle fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="pickerNoticeTitle" style="font-size: 13.5px;">Notice</h6>
                <p class="text-muted small mb-3" id="pickerNoticeMsg" style="font-size: 11.5px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Strict 5px Element Radius */
    #globalMediaPickerModal .modal-content,
    #globalMediaPickerModal .btn,
    #globalMediaPickerModal .form-control,
    #globalMediaPickerModal .form-select,
    #globalMediaPickerModal .input-group-text,
    #globalMediaPickerModal .badge,
    #globalMediaPickerModal .nav-link,
    #pickerNoticeModal .btn {
        border-radius: 5px !important;
    }

    #globalMediaPickerModal .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #globalMediaPickerModal .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Fixed Viewport Modal Canvas */
    .picker-modal-canvas {
        height: 85vh;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        background: #ffffff;
    }
    .picker-modal-viewport {
        flex: 1 1 auto;
        overflow: hidden;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    /* Segmented Header Tabs */
    .picker-segmented-tabs {
        display: inline-flex;
        background: #e2e8f0;
        padding: 2px;
        border-radius: 5px !important;
        gap: 2px;
    }
    .picker-segmented-tabs .nav-link {
        font-size: 11px !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        color: #475569 !important;
        border: none !important;
        background: transparent;
        transition: all 0.15s ease;
    }
    .picker-segmented-tabs .nav-link.active {
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08) !important;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .picker-filter-input,
    .picker-filter-select {
        font-size: 11.5px !important;
        height: 31px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
    }
    .picker-filter-input:focus,
    .picker-filter-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }
    .picker-search-addon {
        height: 31px !important;
        border-color: #cbd5e1 !important;
    }

    /* Media Grid Layout */
    .picker-media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
    }
    .picker-item {
        aspect-ratio: 1 / 1;
        border: 2px solid #e2e8f0;
        border-radius: 5px !important;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        background: #f8fafc;
        transition: transform 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
        user-select: none;
    }
    .picker-item:hover {
        border-color: #94a3b8;
        transform: translateY(-1px);
    }
    .picker-item.selected {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.3) !important;
    }
    .picker-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .picker-item .check-icon {
        display: none;
        position: absolute;
        top: 4px;
        right: 4px;
        background: #2563eb;
        color: #ffffff;
        width: 18px;
        height: 18px;
        border-radius: 4px !important;
        font-size: 9px;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .picker-item.selected .check-icon {
        display: flex;
    }

    /* Details Column */
    .picker-details-column {
        width: 280px;
        min-width: 280px;
        max-width: 280px;
    }

    /* WordPress Style Dropzone */
    .wp-dropzone-box {
        border: 2px dashed #94a3b8;
        border-radius: 6px !important;
        padding: 40px 20px;
        text-align: center;
        background: #f8fafc;
        margin: 10px auto;
        max-width: 540px;
        transition: all 0.2s ease;
    }
    .wp-dropzone-box.dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .picker-modal-canvas {
            height: 92vh;
            max-height: 92vh;
        }
        .picker-segmented-tabs {
            margin-left: auto !important;
        }
        .picker-media-grid {
            grid-template-columns: repeat(auto-fill, minmax(78px, 1fr));
            gap: 6px;
        }
        .picker-details-column {
            width: 100%;
            min-width: 100%;
            max-width: 100%;
            max-height: 200px;
            border-left: 0 !important;
            border-top: 1px solid #e2e8f0;
        }
        .wp-dropzone-box {
            padding: 24px 12px;
        }
    }
</style>

<script>
let mediaPickerConfig = {
    multiple: false,
    onSelect: null
};
let pickerCurrentPage = 1;
let pickerSelectedItems = [];
let pickerSearchTimeout;
window.pickerItemsMap = {};

function showPickerNotice(message, title = 'Notice', isError = false) {
    const titleEl = document.getElementById('pickerNoticeTitle');
    const msgEl = document.getElementById('pickerNoticeMsg');
    const iconWrap = document.getElementById('pickerNoticeIconWrap');
    const icon = document.getElementById('pickerNoticeIcon');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;
    if (iconWrap && icon) {
        iconWrap.className = isError 
            ? 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2' 
            : 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
        icon.className = isError ? 'fas fa-circle-exclamation fs-5' : 'fas fa-check-circle fs-5';
    }

    const modalEl = document.getElementById('pickerNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

/**
 * Universal Global Function to Open Media Picker
 */
window.openMediaPicker = function(options = {}) {
    mediaPickerConfig = {
        multiple: options.multiple || false,
        onSelect: options.onSelect || null
    };

    pickerSelectedItems = [];
    updatePickerFooter();
    document.getElementById('tab-library-btn')?.click();

    const modalEl = document.getElementById('globalMediaPickerModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    fetchPickerMedia(1);
};

// Safe Media Payload Normalizer
function parseMediaResponseItems(res) {
    if (!res) return [];
    if (Array.isArray(res)) return res;
    if (Array.isArray(res.data)) return res.data;
    if (res.data && Array.isArray(res.data.data)) return res.data.data;
    if (Array.isArray(res.media)) return res.media;
    if (res.media && Array.isArray(res.media.data)) return res.media.data;
    return [];
}

// Fetch Media Items via AJAX
function fetchPickerMedia(page = 1) {
    pickerCurrentPage = page;
    const loader = document.getElementById('pickerMediaLoader');
    const grid = document.getElementById('pickerGridWrapper');
    const emptyNotice = document.getElementById('pickerEmptyNotice');
    const loadMoreBtn = document.getElementById('pickerLoadMoreWrap');
    const type = document.getElementById('pickerFilterType')?.value || 'all';
    const search = document.getElementById('pickerSearchInput')?.value.trim() || '';

    if (page === 1) {
        if (grid) grid.innerHTML = '';
        window.pickerItemsMap = {};
        loader?.classList.remove('d-none');
        emptyNotice?.classList.add('d-none');
    }

    const url = new URL("{{ route('admin.media.index') }}", window.location.origin);
    url.searchParams.set('page', page);
    url.searchParams.set('type', type);
    if (search) url.searchParams.set('search', search);

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(res => {
        loader?.classList.add('d-none');
        const items = parseMediaResponseItems(res);

        if (page === 1 && items.length === 0) {
            emptyNotice?.classList.remove('d-none');
            if (loadMoreBtn) loadMoreBtn.style.display = 'none';
            return;
        }

        renderPickerGrid(items, page > 1);

        const hasMore = (res.pagination && res.pagination.has_more) || (res.next_page_url !== null && res.next_page_url !== undefined) || (res.data && res.data.next_page_url);
        if (loadMoreBtn) {
            loadMoreBtn.style.display = hasMore ? 'block' : 'none';
        }
    })
    .catch(err => {
        console.error('Picker fetch error:', err);
        loader?.classList.add('d-none');
        if (page === 1) emptyNotice?.classList.remove('d-none');
    });
}

function loadMorePickerMedia() {
    fetchPickerMedia(pickerCurrentPage + 1);
}

function debouncePickerSearch() {
    clearTimeout(pickerSearchTimeout);
    pickerSearchTimeout = setTimeout(() => {
        fetchPickerMedia(1);
    }, 300);
}

function renderPickerGrid(items, append = false) {
    const grid = document.getElementById('pickerGridWrapper');
    if (!grid) return;
    let html = '';

    items.forEach(item => {
        window.pickerItemsMap[item.id] = item;
        const isImg = item.file_type ? item.file_type.startsWith('image/') : (!item.url || !item.url.endsWith('.pdf'));
        const fileUrl = item.url || (item.file_path ? ('/' + item.file_path.replace(/^\/+/, '')) : '');
        item.resolved_url = fileUrl;

        html += `
            <div class="picker-item" data-id="${item.id}" onclick="handlePickerItemClickById(${item.id}, this)">
                ${isImg 
                    ? `<img src="${fileUrl}" alt="${item.alt_text || ''}" loading="lazy" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'80\\' height=\\'80\\'><rect fill=\\'%23f1f5f9\\' width=\\'80\\' height=\\'80\\'/></svg>'">` 
                    : `<div class="h-100 d-flex flex-column align-items-center justify-content-center p-1 text-center bg-white"><i class="fas fa-file-pdf fa-2x text-danger mb-1"></i><small class="text-truncate w-100 font-monospace bn-digit" style="font-size: 8.5px;">${item.file_name || 'Document'}</small></div>`}
                <div class="check-icon"><i class="fas fa-check"></i></div>
            </div>
        `;
    });

    if (append) {
        grid.insertAdjacentHTML('beforeend', html);
    } else {
        grid.innerHTML = html;
    }
}

function handlePickerItemClickById(id, element) {
    const item = window.pickerItemsMap[id];
    if (!item) return;
    handlePickerItemClick(item, element);
}

function handlePickerItemClick(item, element) {
    if (mediaPickerConfig.multiple) {
        const index = pickerSelectedItems.findIndex(i => i.id === item.id);
        if (index > -1) {
            pickerSelectedItems.splice(index, 1);
            element.classList.remove('selected');
        } else {
            pickerSelectedItems.push(item);
            element.classList.add('selected');
        }
    } else {
        document.querySelectorAll('.picker-item').forEach(el => el.classList.remove('selected'));
        pickerSelectedItems = [item];
        element.classList.add('selected');
    }

    renderPickerDetails(item);
    updatePickerFooter();
}

function renderPickerDetails(item) {
    const placeholder = document.getElementById('pickerNoSelectionPanel');
    const panel = document.getElementById('pickerDetailsPanel');
    if (placeholder) placeholder.style.display = 'none';
    if (panel) panel.style.display = 'block';

    const imgEl = document.getElementById('pickerDetailImg');
    if (imgEl) imgEl.src = item.resolved_url || item.url;
    
    const nameEl = document.getElementById('pickerDetailName');
    if (nameEl) nameEl.innerText = item.file_name || 'Selected Item';

    const dateEl = document.getElementById('pickerDetailDate');
    if (dateEl) dateEl.innerText = 'Uploaded: ' + (item.formatted_date || item.created_at || 'Recent');

    const dimsEl = document.getElementById('pickerDetailDims');
    if (dimsEl) dimsEl.innerText = 'Dimensions: ' + (item.dimensions || 'N/A');

    const sizeEl = document.getElementById('pickerDetailSize');
    if (sizeEl) sizeEl.innerText = 'Size: ' + (item.human_size || item.size || 'N/A');

    const altEl = document.getElementById('pickerDetailAlt');
    if (altEl) altEl.value = item.alt_text || '';

    const titleEl = document.getElementById('pickerDetailTitle');
    if (titleEl) titleEl.value = item.title || item.file_name || '';

    panel?.setAttribute('data-active-id', item.id);
}

function updatePickerFooter() {
    const btn = document.getElementById('pickerInsertBtn');
    const text = document.getElementById('pickerSelectedCountText');
    const thumbWrap = document.getElementById('pickerFooterThumbPreview');
    const thumbImg = document.getElementById('pickerFooterThumbImg');
    const count = pickerSelectedItems.length;

    if (count > 0) {
        if (btn) btn.disabled = false;
        if (text) text.innerText = count + ' item(s) selected';
        const lastItem = pickerSelectedItems[count - 1];
        const lastUrl = lastItem?.resolved_url || lastItem?.url;
        if (lastUrl && thumbWrap && thumbImg) {
            thumbImg.src = lastUrl;
            thumbWrap.classList.remove('d-none');
        }
    } else {
        if (btn) btn.disabled = true;
        if (text) text.innerText = 'No items selected';
        if (thumbWrap) thumbWrap.classList.add('d-none');
    }
}

function savePickerMetaAjax() {
    const panel = document.getElementById('pickerDetailsPanel');
    const id = panel?.getAttribute('data-active-id');
    if (!id) return;

    fetch(`{{ url('admin/media') }}/${id}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            alt_text: document.getElementById('pickerDetailAlt')?.value || '',
            title: document.getElementById('pickerDetailTitle')?.value || ''
        })
    })
    .then(res => res.json())
    .then(data => {
        showPickerNotice('Attachment metadata saved successfully.', 'Saved');
    })
    .catch(() => {
        showPickerNotice('Failed to update metadata.', 'Error', true);
    });
}

function executeMediaSelection() {
    if (typeof mediaPickerConfig.onSelect === 'function') {
        if (mediaPickerConfig.multiple) {
            mediaPickerConfig.onSelect(pickerSelectedItems);
        } else {
            mediaPickerConfig.onSelect(pickerSelectedItems[0] || null);
        }
    }
    const modalEl = document.getElementById('globalMediaPickerModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    } else {
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    }
}

// Upload Files Handling (Tab 2)
function uploadPickerFiles(files) {
    if (!files || !files.length) return;
    const progress = document.getElementById('pickerUploadProgress');
    progress?.classList.remove('d-none');

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    formData.append('_token', token);

    fetch("{{ route('admin.media.upload') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        progress?.classList.add('d-none');
        if (data.status === 'success') {
            document.getElementById('tab-library-btn')?.click();
            fetchPickerMedia(1);
        } else {
            showPickerNotice(data.message || 'Upload failed', 'Upload Error', true);
        }
    })
    .catch(err => {
        progress?.classList.add('d-none');
        console.error('Upload error:', err);
        showPickerNotice('Upload failed. Please check file format and size.', 'Upload Error', true);
    });
}

// Drag & Drop & Nested Modal Setup
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('globalMediaPickerModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function() {
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 1) {
                    backdrops[backdrops.length - 1].classList.add('picker-backdrop');
                    backdrops[backdrops.length - 1].style.zIndex = '105990';
                }
            }, 50);
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (document.querySelectorAll('.modal.show').length > 0) {
                document.body.classList.add('modal-open');
            }
        });
    }

    const dropzone = document.getElementById('wpPickerDropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files.length) {
                uploadPickerFiles(dt.files);
            }
        }, false);
    }
});
</script>