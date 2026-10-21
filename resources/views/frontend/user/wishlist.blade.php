@extends('layouts.dashboard')

@section('title', 'My Wishlist - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    /* ================= SHOP STYLE TOP CONTROLS & FILTER ================= */
    .wishlist-top-controls {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 10px 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .wishlist-sort-select {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        border-color: #cbd5e1;
        background-color: #f8fafc;
        padding: 6px 30px 6px 12px;
        cursor: pointer;
        min-width: 175px;
        transition: all 0.2s ease;
    }
    .wishlist-sort-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        background-color: #ffffff;
    }

    /* ================= 100% RESPONSIVE PAGINATION ================= */
    .wishlist-pagination-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 16px 20px;
        margin-top: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        width: 100%;
    }

    .pagination-counter-text {
        font-size: 13.5px;
        color: #64748b;
        font-weight: 500;
        margin: 0;
        white-space: nowrap;
    }

    .pagination-buttons-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding: 4px 0;
    }
    .pagination-buttons-wrapper::-webkit-scrollbar {
        display: none;
    }

    .pagination-buttons-wrapper nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto !important;
    }
    .pagination-buttons-wrapper nav > div:first-child {
        display: none !important;
    }
    .pagination-buttons-wrapper nav > div:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .pagination-buttons-wrapper nav > div:last-child > div:first-child {
        display: none !important;
    }

    .pagination-buttons-wrapper .pagination {
        margin: 0 auto !important;
        gap: 5px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: nowrap !important;
    }

    .pagination-buttons-wrapper .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-weight: 600;
        font-size: 13px;
        padding: 6px 12px;
        transition: all 0.2s ease;
        margin: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        background: #ffffff;
    }

    .pagination-buttons-wrapper .page-item.active .page-link {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
    }

    .pagination-buttons-wrapper .page-link:hover {
        background-color: #eff6ff;
        color: #2563eb;
        border-color: #93c5fd;
    }

    @media (max-width: 767.98px) {
        .wishlist-pagination-card {
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            gap: 12px !important;
            padding: 14px 10px !important;
        }

        .pagination-counter-text {
            font-size: 12.5px !important;
            text-align: center !important;
            width: 100% !important;
        }

        .pagination-buttons-wrapper {
            width: 100% !important;
            justify-content: center !important;
        }

        .pagination-buttons-wrapper nav {
            width: 100% !important;
            justify-content: center !important;
        }

        .pagination-buttons-wrapper .pagination {
            margin: 0 auto !important;
            justify-content: center !important;
        }

        .pagination-buttons-wrapper .page-link {
            font-size: 12px !important;
            padding: 4px 9px !important;
            min-width: 32px !important;
            height: 32px !important;
        }
    }

    /* ================= SHIMMER SKELETON GLOW ANIMATION ================= */
    .sc-skel-glow {
        background: #f1f5f9;
        background-image: linear-gradient(90deg, #f1f5f9 0px, #e2e8f0 50px, #f1f5f9 100px);
        background-size: 600px 100%;
        animation: scWishlistGlow 1.4s infinite linear;
    }
    @keyframes scWishlistGlow {
        0% { background-position: -300px 0; }
        100% { background-position: 300px 0; }
    }

    /* ================= MOBILE COMPACT GRID ================= */
    @media (max-width: 575.98px) {
        .wishlist-compact-container {
            padding: 12px 10px !important;
            border-radius: 14px !important;
        }
        .wishlist-compact-grid {
            --bs-gutter-x: 8px !important;
            --bs-gutter-y: 8px !important;
        }
        .wishlist-compact-grid .card {
            border-radius: 12px !important;
        }
        .wishlist-compact-grid .card-body {
            padding: 8px !important;
        }
        .wishlist-compact-grid .card-img-top {
            max-height: 125px !important;
            padding: 4px !important;
        }
        .wishlist-compact-grid .card-title {
            font-size: 11.5px !important;
            margin-bottom: 2px !important;
        }
        .wishlist-compact-grid small {
            font-size: 10px !important;
        }
        .wishlist-compact-grid .fs-6 {
            font-size: 12.5px !important;
        }
        .wishlist-compact-grid .btn-sm {
            padding: 4px 6px !important;
            font-size: 10px !important;
        }
        .wishlist-compact-grid .btn-wishlist-toggle {
            width: 28px !important;
            height: 28px !important;
            margin: 4px !important;
        }
        .wishlist-compact-grid .btn-wishlist-toggle i {
            font-size: 12px !important;
        }
    }
</style>

<div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white wishlist-compact-container" id="wishlist-main-card">
    
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1" style="font-size: 1.15rem;">
                <i class="fas fa-heart text-warning" style="color: #ff8a00 !important;"></i> My Wishlist
            </h5>
            <p class="text-muted small mb-0" style="font-size: 11.5px;">Saved products to purchase later</p>
        </div>

        @php
            $hasItems = isset($wishlistProducts) && $wishlistProducts->total() > 0;
        @endphp

        <div class="d-flex align-items-center gap-2">
            <!-- 1-Click Clear All Wishlist Button -->
            <button type="button" 
                    id="btn-clear-all-wishlist" 
                    class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold text-nowrap {{ $hasItems ? '' : 'd-none' }}" 
                    onclick="openClearWishlistModal()">
                <i class="fas fa-trash-alt me-1"></i> Clear All
            </button>

            <a href="{{ route('shop') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold text-nowrap">
                <i class="fas fa-plus me-1"></i> Add Items
            </a>
        </div>
    </div>

    <!-- AJAX Filter & Sorting Bar -->
    <div class="wishlist-top-controls d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 {{ $hasItems ? '' : 'd-none' }}" id="wishlist-controls-bar">
        <div>
            <span id="wishlist-showing-text" class="text-muted small fw-semibold">
                Showing {{ $wishlistProducts->firstItem() ?? 0 }} to {{ $wishlistProducts->lastItem() ?? 0 }} of {{ $wishlistProducts->total() ?? 0 }} items
            </span>
        </div>

        <div class="d-flex align-items-center gap-2 align-self-stretch align-self-sm-auto">
            <label for="wishlistSortFilter" class="small fw-bold text-muted text-nowrap mb-0 d-none d-sm-inline">
                <i class="fas fa-sort-amount-down text-primary me-1"></i> Sort by:
            </label>
            <select id="wishlistSortFilter" class="form-select form-select-sm wishlist-sort-select flex-grow-1 flex-sm-grow-0" onchange="applyAjaxWishlistFilter()">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Items</option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="sale" {{ request('sort') == 'sale' ? 'selected' : '' }}>On Sale Deals</option>
                <option value="in_stock" {{ request('sort') == 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
            </select>
        </div>
    </div>

    <!-- Products Grid Container -->
    <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-xl-3 g-2 g-md-3 wishlist-compact-grid {{ $hasItems ? '' : 'd-none' }}" id="wishlist-grid-container">
        @if($hasItems)
            @foreach($wishlistProducts as $product)
                <div class="col product-card-wrap" id="wishlist-col-{{ $product->id }}">
                    <x-product-card :product="$product" style="modern" btnStyle="pill" />
                </div>
            @endforeach
        @endif
    </div>

    <!-- Empty Wishlist Fallback Message -->
    <div class="text-center py-5 text-muted {{ $hasItems ? 'd-none' : '' }}" id="wishlist-empty-state">
        <i class="far fa-heart fa-3x mb-3 text-warning opacity-50" style="color: #ff8a00 !important;"></i>
        <h6 class="fw-bold text-dark">Your wishlist is empty</h6>
        <p class="small text-secondary mb-3">Save items you love by clicking the heart icon while browsing.</p>
        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold shadow-sm">Explore Products</a>
    </div>

    <!-- Pagination Container -->
    <div id="wishlist-pagination-container">
        @if($hasItems && $wishlistProducts->hasPages())
            <div class="wishlist-pagination-card">
                <p class="pagination-counter-text" id="wishlist-pagination-bottom-summary">
                    Showing {{ $wishlistProducts->firstItem() }} to {{ $wishlistProducts->lastItem() }} of {{ $wishlistProducts->total() }} results
                </p>
                <div class="pagination-buttons-wrapper">
                    {{ $wishlistProducts->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

</div>

<!-- Custom UI Clear Confirmation Modal -->
<div class="modal fade" id="clearWishlistModal" tabindex="-1" aria-hidden="true" style="z-index: 100000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3 border-0 shadow-lg rounded-4">
            <div class="modal-body p-2">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-alt fa-3x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Clear Wishlist?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px;">
                    Are you sure you want to remove all items from your wishlist?
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" id="confirm-clear-wishlist-btn" onclick="executeClearWishlist(this)">
                        Yes, Clear All
                    </button>
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const wishlistBaseRoute = "{{ route('user.wishlist') }}";

    function openClearWishlistModal() {
        if (window.bootstrap && bootstrap.Modal) {
            const modalEl = document.getElementById('clearWishlistModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                return;
            }
        }
        executeClearWishlist();
    }

    function executeClearWishlist(btnEl) {
        const token = document.querySelector('meta[name="csrf-token"]') 
            ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            : '{{ csrf_token() }}';

        if (btnEl) btnEl.disabled = true;

        fetch("{{ route('wishlist.clear') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (btnEl) btnEl.disabled = false;

            // Modal hide
            const modalEl = document.getElementById('clearWishlistModal');
            if (modalEl && window.bootstrap && bootstrap.Modal) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

            if (data.status === true || data.status === 'success') {
                const grid = document.getElementById('wishlist-grid-container');
                if (grid) {
                    grid.innerHTML = '';
                    grid.classList.add('d-none');
                }

                const controls = document.getElementById('wishlist-controls-bar');
                if (controls) controls.classList.add('d-none');

                const pag = document.getElementById('wishlist-pagination-container');
                if (pag) pag.innerHTML = '';

                const clearBtn = document.getElementById('btn-clear-all-wishlist');
                if (clearBtn) clearBtn.classList.add('d-none');

                const emptyState = document.getElementById('wishlist-empty-state');
                if (emptyState) emptyState.classList.remove('d-none');

                // Header Saved Counter Badge Reset
                const badge = document.getElementById('wishlist-counter-badge');
                if (badge) {
                    badge.innerText = '0';
                    badge.classList.add('d-none');
                    badge.style.display = 'none';
                }

                const savedIcon = document.getElementById('header-saved-icon');
                if (savedIcon) {
                    savedIcon.className = 'far fa-heart';
                    savedIcon.style.color = '';
                }

                const savedText = document.getElementById('header-saved-text');
                if (savedText) {
                    savedText.style.color = '';
                    savedText.style.fontWeight = '';
                }

                document.querySelectorAll('.btn-wishlist-toggle').forEach(el => {
                    el.classList.remove('is-active');
                    const iTag = el.querySelector('i');
                    if (iTag) iTag.className = 'far fa-heart';
                });
            }
        })
        .catch(err => {
            if (btnEl) btnEl.disabled = false;
            console.error('Clear Wishlist Error:', err);
        });
    }

    function getWishlistSkeletonHTML(count) {
        let total = count || 6;
        let skel = '';
        for (let i = 0; i < total; i++) {
            skel += '<div class="col product-card-wrap">' +
                '<div style="background:#ffffff; border-radius:12px; border:1px solid #edf2f7; padding:10px; display:flex; flex-direction:column; gap:8px;">' +
                    '<div class="sc-skel-glow" style="width:100%; aspect-ratio:1/1; border-radius:10px;"></div>' +
                    '<div class="sc-skel-glow" style="height:14px; width:80%; border-radius:4px;"></div>' +
                    '<div class="sc-skel-glow" style="height:16px; width:45%; border-radius:4px;"></div>' +
                    '<div class="sc-skel-glow" style="height:32px; width:100%; border-radius:20px; margin-top:4px;"></div>' +
                '</div>' +
            '</div>';
        }
        return skel;
    }

    function loadWishlistProductsAjax(url, browserDisplayUrl) {
        const grid = document.getElementById('wishlist-grid-container');
        const cardCount = grid ? (grid.children.length || 6) : 6;

        if (grid) {
            grid.innerHTML = getWishlistSkeletonHTML(cardCount);
            grid.style.opacity = '1';
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newGrid = doc.getElementById('wishlist-grid-container');
            const newShowing = doc.getElementById('wishlist-showing-text');
            const newPag = doc.getElementById('wishlist-pagination-container');
            const newEmpty = doc.getElementById('wishlist-empty-state');
            const newControls = doc.getElementById('wishlist-controls-bar');
            const newClearBtn = doc.getElementById('btn-clear-all-wishlist');

            if (grid && newGrid) {
                grid.innerHTML = newGrid.innerHTML;
                grid.className = newGrid.className;
            }

            const currentShowing = document.getElementById('wishlist-showing-text');
            if (currentShowing && newShowing) {
                currentShowing.innerHTML = newShowing.innerHTML;
            }

            const currentPag = document.getElementById('wishlist-pagination-container');
            if (currentPag && newPag) {
                currentPag.innerHTML = newPag.innerHTML;
            }

            const currentEmpty = document.getElementById('wishlist-empty-state');
            if (currentEmpty && newEmpty) {
                currentEmpty.className = newEmpty.className;
            }

            const currentControls = document.getElementById('wishlist-controls-bar');
            if (currentControls && newControls) {
                currentControls.className = newControls.className;
            }

            const clearBtn = document.getElementById('btn-clear-all-wishlist');
            if (clearBtn && newClearBtn) {
                clearBtn.className = newClearBtn.className;
            }

            const targetHistoryUrl = browserDisplayUrl || url;
            window.history.pushState({ path: targetHistoryUrl }, '', targetHistoryUrl);

            const card = document.getElementById('wishlist-main-card');
            if (card) {
                const rect = card.getBoundingClientRect();
                if (rect.top < 0) {
                    window.scrollTo({
                        top: window.scrollY + rect.top - 80,
                        behavior: 'smooth'
                    });
                }
            }
        })
        .catch(err => {
            console.error('Wishlist AJAX Error:', err);
            window.location.href = url;
        });
    }

    function applyAjaxWishlistFilter() {
        const sortFilterEl = document.getElementById('wishlistSortFilter');
        const sortVal = sortFilterEl ? sortFilterEl.value : 'latest';

        const currentBrowserUrl = new URL(window.location.href);
        currentBrowserUrl.searchParams.set('sort', sortVal);
        currentBrowserUrl.searchParams.set('page', '1');

        loadWishlistProductsAjax(currentBrowserUrl.toString(), currentBrowserUrl.toString());
    }

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#wishlist-pagination-container .page-link');
        if (pageLink) {
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                e.preventDefault();

                const clickedUrl = new URL(href, window.location.origin);
                const pageNum = clickedUrl.searchParams.get('page') || '1';
                const sortFilterEl = document.getElementById('wishlistSortFilter');
                const sortVal = sortFilterEl ? sortFilterEl.value : (clickedUrl.searchParams.get('sort') || 'latest');

                const currentBrowserUrl = new URL(window.location.href);
                currentBrowserUrl.searchParams.set('sort', sortVal);
                currentBrowserUrl.searchParams.set('page', pageNum);

                loadWishlistProductsAjax(currentBrowserUrl.toString(), currentBrowserUrl.toString());
            }
        }
    });

    window.addEventListener('popstate', function() {
        applyAjaxWishlistFilter();
    });
</script>
@endsection