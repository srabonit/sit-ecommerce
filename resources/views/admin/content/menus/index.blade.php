@extends('layouts.admin')

@section('title', 'Navigation Menus - ' . config('app.name'))

@section('admin_content')
@php
    $currentLocations = !empty($currentMenu->locations) ? json_decode($currentMenu->locations, true) : [];
    $currentItems = !empty($currentMenu->items) ? json_decode($currentMenu->items, true) : [];
@endphp

<style>
    /* Global Strict 5px Element Radius */
    #navMenusRoot .btn,
    #navMenusRoot .btn-sm,
    #navMenusRoot button,
    #navMenusRoot .form-control,
    #navMenusRoot .form-select,
    #menuNoticeModal .btn,
    #deleteMenuConfirmModal .btn {
        border-radius: 5px !important;
    }

    /* Compact & Sleek Button Sizing */
    #navMenusRoot .btn-compact,
    #menuNoticeModal .btn-compact,
    #deleteMenuConfirmModal .btn-compact {
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

    /* Navigation Tabs */
    .wp-nav-tab-wrapper {
        border-bottom: 1px solid #cbd5e1;
        margin-bottom: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .wp-nav-tab {
        border: 1px solid #cbd5e1;
        border-bottom: none;
        background: #f1f5f9;
        color: #475569;
        padding: 7px 16px;
        display: inline-block;
        font-weight: 600;
        font-size: 12.5px;
        text-decoration: none;
        border-radius: 6px 6px 0 0;
        margin-bottom: -1px;
        transition: all 0.15s ease;
    }
    .wp-nav-tab:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .wp-nav-tab.active {
        background: #ffffff;
        border-bottom: 1px solid #ffffff;
        color: #2563eb;
        font-weight: 700;
    }

    /* Menu Selector Bar */
    .wp-menu-select-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 18px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    /* Form Controls */
    #navMenusRoot .form-control,
    #navMenusRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #navMenusRoot .form-control:focus,
    #navMenusRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Left Column Accordions */
    .wp-accordion-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        margin-bottom: 10px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .wp-accordion-header {
        background: #f8fafc;
        border-bottom: 1px solid transparent;
        padding: 10px 14px;
        font-weight: 600;
        font-size: 13px;
        color: #1e293b;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
        transition: background-color 0.15s ease;
    }
    .wp-accordion-header:hover {
        background: #f1f5f9;
    }
    .wp-accordion-body {
        padding: 12px 14px;
        border-top: 1px solid #e2e8f0;
    }

    /* Right Column Structure Box */
    .wp-structure-box {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .wp-structure-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .wp-structure-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 12px 16px;
    }

    /* Multi-level Indentation & Depth Styling */
    .menu-item-bar {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        border-radius: 6px;
        margin-bottom: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        transition: margin-left 0.2s ease, border-color 0.15s ease;
        overflow: hidden;
    }
    .menu-item-bar:hover {
        border-color: #94a3b8;
    }
    .menu-item-bar.depth-0 { margin-left: 0px; }
    .menu-item-bar.depth-1 { margin-left: 28px; border-left: 3px solid #2563eb; }
    .menu-item-bar.depth-2 { margin-left: 56px; border-left: 3px solid #10b981; }
    .menu-item-bar.depth-3 { margin-left: 84px; border-left: 3px solid #f59e0b; }

    .menu-item-handle {
        padding: 9px 12px;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12.5px;
        color: #0f172a;
        cursor: pointer;
        user-select: none;
    }
    .menu-item-handle:hover {
        background: #f1f5f9;
    }
    .menu-item-settings {
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        padding: 12px;
        display: none;
    }
    .menu-item-bar.is-open .menu-item-settings {
        display: block;
    }

    /* Inner Tabs & Scrollable Lists */
    .tab-content-nav .nav-link {
        color: #64748b;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 10px;
        border: none;
        border-bottom: 2px solid transparent;
    }
    .tab-content-nav .nav-link.active {
        color: #2563eb;
        font-weight: 700;
        border-bottom: 2px solid #2563eb;
        background: transparent;
    }
    .accordion-scroll-list {
        max-height: 175px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .accordion-scroll-list::-webkit-scrollbar {
        width: 4px;
    }
    .accordion-scroll-list::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }

    /* Checkboxes */
    .chk-item, .loc-check, #auto-add-pages {
        width: 15px;
        height: 15px;
        accent-color: #2563eb;
        cursor: pointer;
        flex-shrink: 0;
    }
    .cursor-pointer {
        cursor: pointer;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile 100/100 Optimizations */
    @media (max-width: 767.98px) {
        .wp-nav-tab {
            flex: 1 1 0;
            text-align: center;
            padding: 7px 10px;
            font-size: 12px;
        }
        .wp-menu-select-bar {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px !important;
            padding: 12px;
        }
        .wp-menu-select-bar form {
            flex-direction: row !important;
            align-items: center !important;
            gap: 6px !important;
            width: 100%;
        }
        .wp-menu-select-bar form select {
            min-width: 0 !important;
            flex: 1 1 auto;
        }
        .wp-structure-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 10px;
            padding: 12px;
        }
        .wp-structure-header .menu-name-input-group {
            max-width: 100% !important;
            width: 100%;
        }
        .wp-structure-header .btn-save-top {
            width: 100%;
        }
        /* Compact Indentation on Small Mobile Screens */
        .menu-item-bar.depth-1 { margin-left: 14px; }
        .menu-item-bar.depth-2 { margin-left: 28px; }
        .menu-item-bar.depth-3 { margin-left: 42px; }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="navMenusRoot">
    <!-- Top Mode Tabs -->
    <div class="wp-nav-tab-wrapper">
        <a href="{{ route('admin.content.menus.index') }}" class="wp-nav-tab active">Edit Menus</a>
        <a href="{{ route('admin.content.menus.create') }}" class="wp-nav-tab">Create New Menu</a>
    </div>

    <!-- Select Menu Dropdown Bar -->
    <div class="wp-menu-select-bar d-flex align-items-center flex-wrap gap-2">
        <label class="small fw-semibold text-dark mb-0" style="font-size: 12px;">Select a menu to edit:</label>
        <form method="GET" action="{{ route('admin.content.menus.index') }}" class="d-flex align-items-center gap-2">
            <select name="menu" class="form-select form-select-sm fw-semibold" style="min-width: 220px;">
                @foreach($allMenus as $m)
                    @php
                        $locArray = json_decode($m->locations, true) ?: [];
                        $locString = !empty($locArray) ? ' (' . implode(', ', array_map('ucfirst', $locArray)) . ')' : '';
                    @endphp
                    <option value="{{ $m->id }}" {{ optional($currentMenu)->id == $m->id ? 'selected' : '' }}>
                        {{ $m->name }}{{ $locString }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-primary btn-compact shadow-xs">Select</button>
        </form>
        <span class="small text-muted" style="font-size: 12px;">or <a href="{{ route('admin.content.menus.create') }}" class="text-primary text-decoration-none fw-semibold">create a new menu</a>.</span>
    </div>

    <div class="row g-3">
        <!-- Left Column: Add Menu Items -->
        <div class="col-12 col-lg-4">
            <h6 class="fw-bold mb-2 text-dark" style="font-size: 13.5px;">Add menu items</h6>

            <!-- 1. Pages Accordion -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Pages</span>
                    <i class="fas fa-chevron-up small text-muted"></i>
                </div>
                <div class="wp-accordion-body">
                    <ul class="nav nav-tabs tab-content-nav border-bottom mb-2" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pages-recent" type="button">Most Recent</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pages-all" type="button">View All</button></li>
                    </ul>
                    <div class="tab-content accordion-scroll-list">
                        <div class="tab-pane fade show active" id="pages-recent">
                            @forelse($pages->take(8) as $p)
                                <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                    <input type="checkbox" class="chk-item" data-title="{{ $p->title }}" data-url="/{{ $p->slug }}" data-type="Page">
                                    <span class="text-break">{{ $p->title }}</span>
                                </label>
                            @empty
                                <div class="small text-muted py-2">No pages found.</div>
                            @endforelse
                        </div>
                        <div class="tab-pane fade" id="pages-all">
                            @forelse($pages as $p)
                                <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                    <input type="checkbox" class="chk-item" data-title="{{ $p->title }}" data-url="/{{ $p->slug }}" data-type="Page">
                                    <span class="text-break">{{ $p->title }}</span>
                                </label>
                            @empty
                                <div class="small text-muted py-2">No pages found.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- 2. Store Endpoints -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Store Endpoints</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>
                <div class="wp-accordion-body" style="display: none;">
                    <div class="accordion-scroll-list">
                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;"><input type="checkbox" class="chk-item" data-title="Shop" data-url="{{ route('shop') }}" data-type="Store Page"><span class="text-break">Shop (Storefront)</span></label>
                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;"><input type="checkbox" class="chk-item" data-title="Cart" data-url="{{ route('cart.index') }}" data-type="Store Page"><span class="text-break">Cart</span></label>
                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;"><input type="checkbox" class="chk-item" data-title="Checkout" data-url="{{ route('checkout') }}" data-type="Store Page"><span class="text-break">Checkout</span></label>
                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;"><input type="checkbox" class="chk-item" data-title="My Account" data-url="{{ route('user.dashboard') }}" data-type="Store Page"><span class="text-break">My Account</span></label>
                        <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;"><input type="checkbox" class="chk-item" data-title="Track Order" data-url="{{ route('guest.track.order') }}" data-type="Store Page"><span class="text-break">Track Order</span></label>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- 3. Product Categories -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Product Categories</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>
                <div class="wp-accordion-body" style="display: none;">
                    <div class="accordion-scroll-list">
                        @foreach($categories as $cat)
                            <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                <input type="checkbox" class="chk-item" data-title="{{ $cat->name }}" data-url="{{ route('category.products', $cat->slug ?? $cat->id) }}" data-type="Category">
                                <span class="text-break">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- 4. Posts -->
            <div class="wp-accordion-card">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Posts</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>
                <div class="wp-accordion-body" style="display: none;">
                    <div class="accordion-scroll-list">
                        @foreach($blogPosts as $bp)
                            <label class="d-flex align-items-center gap-2 small py-1 cursor-pointer text-dark" style="font-size: 12px;">
                                <input type="checkbox" class="chk-item" data-title="{{ $bp->title }}" data-url="/blog/{{ $bp->slug }}" data-type="Post">
                                <span class="text-break">{{ $bp->title }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="selectAllInAccordion(this)">Select All</button>
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCheckedItemsToMenu(this)">Add to Menu</button>
                    </div>
                </div>
            </div>

            <!-- 5. Custom Links -->
            <div class="wp-accordion-card mb-0">
                <div class="wp-accordion-header" onclick="toggleWpAccordion(this)">
                    <span>Custom Links</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>
                <div class="wp-accordion-body" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">URL</label>
                        <input type="text" id="cl-url" class="form-control form-control-sm font-monospace" placeholder="https://">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Link Text</label>
                        <input type="text" id="cl-text" class="form-control form-control-sm" placeholder="Menu Title">
                    </div>
                    <div class="text-end pt-2 border-top mt-2">
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="addCustomLink()">Add to Menu</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Structure & Multi-level Depth Settings -->
        <div class="col-12 col-lg-8">
            <div class="wp-structure-box">
                <div class="wp-structure-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 menu-name-input-group" style="max-width: 65%; width: 100%;">
                        <label class="small fw-bold text-dark text-nowrap mb-0" style="font-size: 12px;">Menu Name</label>
                        <input type="text" id="menu-name" class="form-control form-control-sm fw-semibold" value="{{ optional($currentMenu)->name ?? 'Main Menu' }}" required>
                        <input type="hidden" id="menu-id" value="{{ optional($currentMenu)->id ?? '' }}">
                    </div>
                    <button type="button" class="btn btn-primary btn-compact px-3 shadow-xs btn-save-top" onclick="submitMenuForm()">Save Menu</button>
                </div>

                <div class="p-3 bg-light bg-opacity-50 border-bottom">
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Menu structure</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.4;">Drag items to arrange order. Use <strong>Move Right</strong> (Sub item) or <strong>Move Left</strong> (Parent) to create multi-level menus.</p>

                    <div id="wp-menu-items-canvas">
                        @forelse($currentItems as $item)
                            @php $depth = $item['depth'] ?? ($item['is_sub'] ?? false ? 1 : 0); @endphp
                            <div class="menu-item-bar depth-{{ $depth }}" 
                                 data-url="{{ $item['url'] }}" 
                                 data-type="{{ $item['type'] ?? 'Custom' }}" 
                                 data-original="{{ $item['original_title'] ?? $item['title'] }}"
                                 data-depth="{{ $depth }}">
                                <div class="menu-item-handle" onclick="toggleItemAccordion(this)">
                                    <span class="item-title-display fw-semibold text-break pe-2">{{ $item['title'] }}</span>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="text-muted small item-type-badge bn-digit" style="font-size: 11px;">{{ $depth > 0 ? 'Sub item (Depth ' . $depth . ')' : ($item['type'] ?? 'Custom') }}</span>
                                        <i class="fas fa-caret-down text-secondary"></i>
                                    </div>
                                </div>
                                <div class="menu-item-settings">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Navigation Label</label>
                                        <input type="text" class="form-control form-control-sm item-nav-label" value="{{ $item['title'] }}" oninput="updateItemTitle(this)">
                                    </div>
                                    <div class="mb-2 d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-compact" onclick="indentItem(this, 1)">
                                            <i class="fas fa-arrow-right me-1" style="font-size: 10px;"></i>
                                            <span>Move Right</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-compact" onclick="indentItem(this, -1)">
                                            <i class="fas fa-arrow-left me-1" style="font-size: 10px;"></i>
                                            <span>Move Left</span>
                                        </button>
                                    </div>
                                    <div class="small text-muted mb-2 text-break" style="font-size: 11.5px;">Original: <span class="fw-semibold text-dark">{{ $item['original_title'] ?? $item['title'] }}</span></div>
                                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                        <button type="button" class="btn btn-outline-danger btn-compact" onclick="removeMenuItem(this)">Remove</button>
                                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="toggleItemAccordion(this)">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted border rounded bg-white small" id="empty-canvas-notice" style="border-style: dashed !important;">
                                Add menu items from the column on the left.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Menu Locations -->
                <div class="p-3 bg-white">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-size: 13.5px;">Menu Settings</h6>
                    
                    <div class="row mb-3 align-items-center">
                        <div class="col-12 col-sm-3 text-muted small fw-semibold mb-1 mb-sm-0" style="font-size: 12px;">Auto add pages</div>
                        <div class="col-12 col-sm-9">
                            <label class="small cursor-pointer d-flex align-items-center gap-2 text-dark" style="font-size: 12px;">
                                <input type="checkbox" id="auto-add-pages" {{ optional($currentMenu)->auto_add_pages ? 'checked' : '' }}>
                                <span>Automatically add new top-level pages to this menu</span>
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-3 text-muted small fw-semibold mb-1 mb-sm-0" style="font-size: 12px;">Display location</div>
                        <div class="col-12 col-sm-9">
                            <div class="d-flex flex-column gap-2 small" style="font-size: 12px;">
                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="primary" {{ in_array('primary', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Primary Menu</strong> <span class="text-muted">(Main Desktop Header Navbar)</span></span>
                                </label>
                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="mobile" {{ in_array('mobile', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Mobile Menu</strong> <span class="text-muted">(Drawer Menu &amp; Pages Tab)</span></span>
                                </label>
                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="mobile_categories" {{ in_array('mobile_categories', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Mobile Categories Menu</strong> <span class="text-muted">(Dedicated Mobile Category Drawer &amp; Shop Sidebar)</span></span>
                                </label>
                                <label class="cursor-pointer d-flex align-items-center gap-2 text-dark">
                                    <input type="checkbox" class="loc-check" value="footer" {{ in_array('footer', $currentLocations) ? 'checked' : '' }}>
                                    <span><strong>Footer Menu</strong> <span class="text-muted">(Quick Links in Footer)</span></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Bar -->
                <div class="wp-structure-footer d-flex justify-content-between align-items-center">
                    @if(!empty($currentMenu))
                        <form action="{{ route('admin.content.menus.delete', $currentMenu->id) }}" method="POST" id="deleteMenuForm" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-compact" onclick="openDeleteMenuModal()">Delete Menu</button>
                        </form>
                    @else
                        <span></span>
                    @endif
                    <button type="button" class="btn btn-primary btn-compact px-3 shadow-xs" onclick="submitMenuForm()">Save Menu</button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Notice / Alert Dialog (Replaces browser alert()) -->
<div class="modal fade" id="menuNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-warning">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="menuNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="menuNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Delete Menu Confirmation Dialog (Replaces browser confirm()) -->
<div class="modal fade" id="deleteMenuConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-alt fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Menu?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">Are you sure you want to permanently delete this navigation menu?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" onclick="document.getElementById('deleteMenuForm').submit()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('wp-menu-items-canvas');
        if (canvas) {
            new Sortable(canvas, { animation: 150, ghostClass: 'bg-primary-subtle' });
        }
    });

    function showMenuNotice(message, title = 'Notice') {
        document.getElementById('menuNoticeTitle').innerText = title;
        document.getElementById('menuNoticeMsg').innerText = message;
        const modalEl = document.getElementById('menuNoticeModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function openDeleteMenuModal() {
        const modalEl = document.getElementById('deleteMenuConfirmModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function toggleWpAccordion(header) {
        const body = header.nextElementSibling;
        const icon = header.querySelector('.fa-chevron-down, .fa-chevron-up');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            if (icon) { icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-up'); }
        } else {
            body.style.display = 'none';
            if (icon) { icon.classList.remove('fa-chevron-up'); icon.classList.add('fa-chevron-down'); }
        }
    }

    function toggleItemAccordion(el) { el.closest('.menu-item-bar').classList.toggle('is-open'); }
    function updateItemTitle(input) { input.closest('.menu-item-bar').querySelector('.item-title-display').innerText = input.value || 'Untitled'; }

    function indentItem(btn, direction) {
        const bar = btn.closest('.menu-item-bar');
        let currentDepth = parseInt(bar.dataset.depth) || 0;
        let newDepth = currentDepth + direction;
        
        if (newDepth < 0) newDepth = 0;
        if (newDepth > 3) newDepth = 3;

        bar.classList.remove('depth-0', 'depth-1', 'depth-2', 'depth-3');
        bar.classList.add('depth-' + newDepth);
        bar.dataset.depth = newDepth;

        const badge = bar.querySelector('.item-type-badge');
        badge.innerText = newDepth > 0 ? 'Sub item (Depth ' + newDepth + ')' : bar.dataset.type;
    }

    function removeMenuItem(link) { link.closest('.menu-item-bar').remove(); checkEmptyCanvas(); }

    function checkEmptyCanvas() {
        const canvas = document.getElementById('wp-menu-items-canvas');
        if (canvas.querySelectorAll('.menu-item-bar').length === 0) {
            canvas.innerHTML = '<div class="p-4 text-center text-muted border rounded bg-white small" id="empty-canvas-notice" style="border-style: dashed !important;">Add menu items from the column on the left.</div>';
        }
    }

    function selectAllInAccordion(btn) {
        const checkboxes = btn.closest('.wp-accordion-body').querySelectorAll('.chk-item');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        checkboxes.forEach(c => c.checked = !allChecked);
        btn.innerText = allChecked ? 'Select All' : 'Deselect All';
    }

    function addCheckedItemsToMenu(btn) {
        const container = btn.closest('.wp-accordion-body');
        const checkedBoxes = container.querySelectorAll('.chk-item:checked');
        if (!checkedBoxes.length) {
            showMenuNotice('Please select at least one item to add to the menu.');
            return;
        }

        const emptyNotice = document.getElementById('empty-canvas-notice');
        if (emptyNotice) emptyNotice.remove();

        const canvas = document.getElementById('wp-menu-items-canvas');
        checkedBoxes.forEach(chk => {
            const title = chk.dataset.title;
            const url = chk.dataset.url;
            const type = chk.dataset.type;

            const itemHtml = `
                <div class="menu-item-bar depth-0" data-url="${url}" data-type="${type}" data-original="${title}" data-depth="0">
                    <div class="menu-item-handle" onclick="toggleItemAccordion(this)">
                        <span class="item-title-display fw-semibold text-break pe-2">${title}</span>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="text-muted small item-type-badge bn-digit" style="font-size: 11px;">${type}</span>
                            <i class="fas fa-caret-down text-secondary"></i>
                        </div>
                    </div>
                    <div class="menu-item-settings">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Navigation Label</label>
                            <input type="text" class="form-control form-control-sm item-nav-label" value="${title}" oninput="updateItemTitle(this)">
                        </div>
                        <div class="mb-2 d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-compact" onclick="indentItem(this, 1)">
                                <i class="fas fa-arrow-right me-1" style="font-size: 10px;"></i>
                                <span>Move Right</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-compact" onclick="indentItem(this, -1)">
                                <i class="fas fa-arrow-left me-1" style="font-size: 10px;"></i>
                                <span>Move Left</span>
                            </button>
                        </div>
                        <div class="small text-muted mb-2 text-break" style="font-size: 11.5px;">Original: <span class="fw-semibold text-dark">${title}</span></div>
                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                            <button type="button" class="btn btn-outline-danger btn-compact" onclick="removeMenuItem(this)">Remove</button>
                            <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="toggleItemAccordion(this)">Cancel</button>
                        </div>
                    </div>
                </div>
            `;
            canvas.insertAdjacentHTML('beforeend', itemHtml);
            chk.checked = false;
        });
    }

    function addCustomLink() {
        const urlInput = document.getElementById('cl-url');
        const textInput = document.getElementById('cl-text');
        const url = urlInput.value.trim();
        const text = textInput.value.trim();

        if (!url || !text) {
            showMenuNotice('Please provide both URL and Link Text.');
            return;
        }

        const emptyNotice = document.getElementById('empty-canvas-notice');
        if (emptyNotice) emptyNotice.remove();

        const canvas = document.getElementById('wp-menu-items-canvas');
        const itemHtml = `
            <div class="menu-item-bar depth-0" data-url="${url}" data-type="Custom Link" data-original="${text}" data-depth="0">
                <div class="menu-item-handle" onclick="toggleItemAccordion(this)">
                    <span class="item-title-display fw-semibold text-break pe-2">${text}</span>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="text-muted small item-type-badge bn-digit" style="font-size: 11px;">Custom Link</span>
                        <i class="fas fa-caret-down text-secondary"></i>
                    </div>
                </div>
                <div class="menu-item-settings">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 11px;">Navigation Label</label>
                        <input type="text" class="form-control form-control-sm item-nav-label" value="${text}" oninput="updateItemTitle(this)">
                    </div>
                    <div class="mb-2 d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-compact" onclick="indentItem(this, 1)">
                            <i class="fas fa-arrow-right me-1" style="font-size: 10px;"></i>
                            <span>Move Right</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-compact" onclick="indentItem(this, -1)">
                            <i class="fas fa-arrow-left me-1" style="font-size: 10px;"></i>
                            <span>Move Left</span>
                        </button>
                    </div>
                    <div class="small text-muted mb-2 text-break" style="font-size: 11.5px;">Original: <span class="fw-semibold text-dark">${text}</span></div>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-outline-danger btn-compact" onclick="removeMenuItem(this)">Remove</button>
                        <button type="button" class="btn btn-light border btn-compact text-secondary" onclick="toggleItemAccordion(this)">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        canvas.insertAdjacentHTML('beforeend', itemHtml);
        urlInput.value = '';
        textInput.value = '';
    }

    function submitMenuForm() {
        const menuName = document.getElementById('menu-name').value.trim();
        if (!menuName) {
            showMenuNotice('Please provide a menu name.');
            return;
        }

        const items = [];
        document.querySelectorAll('#wp-menu-items-canvas .menu-item-bar').forEach(bar => {
            items.push({
                title: bar.querySelector('.item-nav-label').value.trim(),
                url: bar.dataset.url,
                type: bar.dataset.type,
                original_title: bar.dataset.original,
                depth: parseInt(bar.dataset.depth) || 0,
                is_sub: (parseInt(bar.dataset.depth) || 0) > 0
            });
        });

        const locations = [];
        document.querySelectorAll('.loc-check:checked').forEach(c => locations.push(c.value));

        const payload = {
            _token: '{{ csrf_token() }}',
            menu_id: document.getElementById('menu-id').value,
            name: menuName,
            items: items,
            locations: locations,
            auto_add_pages: document.getElementById('auto-add-pages').checked
        };

        fetch('{{ route("admin.content.menus.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect;
            } else {
                showMenuNotice('Failed to save menu.', 'Save Error');
            }
        })
        .catch(() => showMenuNotice('Failed to save menu.', 'Save Error'));
    }
</script>
@endsection