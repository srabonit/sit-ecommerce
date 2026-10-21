<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIT Page Builder — {{ $item->title ?? 'New Page' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Hind+Siliguri:wght@300;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --el-font-sans: 'Plus Jakarta Sans', 'Hind Siliguri', -apple-system, BlinkMacSystemFont, sans-serif;
            --el-font-mono: 'Fira Code', monospace;
            --el-top-h: 48px;
            --el-sidebar-w: 320px;
            --el-dark-top: #0f172a;
            --el-sidebar-bg: #ffffff;
            --el-canvas-bg: #334155;
            --el-accent: #2563eb;
            --el-accent-hover: #1d4ed8;
            --el-pink: #e11d48;
            --el-border: #e2e8f0;
            --el-text-main: #0f172a;
            --el-text-label: #475569;
        }

        * { box-sizing: border-box; }
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: var(--el-font-sans);
            background: var(--el-canvas-bg);
            color: var(--el-text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Global Strict 5px Radius for Controls & Buttons */
        .btn,
        .btn-sm,
        .el-top-btn,
        .el-resp-btn,
        .el-btn-publish-green,
        .el-input-text,
        .el-select,
        .el-quad-box input,
        .el-quad-link,
        .el-color-picker-thumb,
        .el-repeater-box,
        .el-icon-selector-frame,
        .el-tree-item,
        .el-tree-rename-input,
        .sit-toast-alert {
            border-radius: 5px !important;
        }

        /* Safe Bengali Digits */
        .bn-digit,
        .font-monospace {
            font-weight: 700 !important;
        }

        /* Topbar */
        .el-topbar {
            height: var(--el-top-h);
            background: var(--el-dark-top);
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            z-index: 1060;
            position: relative;
        }
        .el-top-left, .el-top-right {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .el-top-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        .el-top-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.14);
        }
        .el-doc-badge {
            font-size: 12px;
            color: #f8fafc;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 260px;
        }
        .el-responsive-switch {
            display: flex;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 5px;
            padding: 2px;
            gap: 2px;
        }
        .el-resp-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 12px;
            padding: 4px 10px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .el-resp-btn.active, .el-resp-btn:hover {
            color: #ffffff;
            background: var(--el-accent);
        }

        .el-btn-publish-green {
            background: #16a34a;
            color: #ffffff;
            border: none;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 16px;
            cursor: pointer;
            transition: background-color 0.15s ease;
            white-space: nowrap;
        }
        .el-btn-publish-green:hover {
            background: #15803d;
        }

        /* Workspace */
        .el-workspace {
            display: flex;
            height: calc(100% - var(--el-top-h));
            width: 100%;
            position: relative;
        }

        /* Sidebar Inspector */
        .el-sidebar {
            width: var(--el-sidebar-w);
            min-width: var(--el-sidebar-w);
            background: var(--el-sidebar-bg);
            border-right: 1px solid var(--el-border);
            display: flex;
            flex-direction: column;
            z-index: 1050;
            user-select: none;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .el-sidebar-header-title {
            padding: 10px 14px;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--el-text-main);
            background: #f8fafc;
            border-bottom: 1px solid var(--el-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .el-panel-tabs {
            display: flex;
            border-bottom: 1px solid var(--el-border);
            height: 38px;
            background: #ffffff;
        }
        .el-tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.15s ease;
        }
        .el-tab-btn:hover {
            color: var(--el-text-main);
            background: #f8fafc;
        }
        .el-tab-btn.active {
            color: var(--el-accent);
            font-weight: 700;
            border-bottom: 2px solid var(--el-accent);
        }

        .el-sidebar-body {
            flex: 1;
            overflow-y: auto;
            padding: 12px 14px;
        }
        .el-sidebar-body::-webkit-scrollbar { width: 5px; }
        .el-sidebar-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        /* Inspector Accordions & Fields */
        .el-inspector-section {
            border-bottom: 1px solid var(--el-border);
            padding: 4px 0;
        }
        .el-inspector-section-header {
            font-size: 12px;
            font-weight: 700;
            color: var(--el-text-main);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            user-select: none;
        }
        .el-inspector-section.collapsed .el-inspector-section-body {
            display: none;
        }
        .el-inspector-section-body {
            padding: 4px 0 10px 0;
        }

        .el-field-row {
            margin-bottom: 10px;
        }
        .el-field-row:last-child { margin-bottom: 0; }
        .el-field-label {
            font-size: 11px;
            color: var(--el-text-label);
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
        }
        .el-input-text, .el-select {
            width: 100%;
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 12px;
            color: var(--el-text-main);
            outline: none;
            background: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .el-input-text:focus, .el-select:focus {
            border-color: var(--el-accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Icon List Repeater UI */
        .el-repeater-box {
            border: 1px solid var(--el-border);
            margin-bottom: 6px;
            background: #ffffff;
            overflow: hidden;
        }
        .el-repeater-head {
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
        }
        .el-repeater-body {
            padding: 10px;
            background: #ffffff;
        }

        .el-icon-selector-frame {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            padding: 12px;
            text-align: center;
            font-size: 22px;
            color: #475569;
            cursor: pointer;
            margin-bottom: 8px;
            transition: all 0.15s ease;
        }
        .el-icon-selector-frame:hover {
            border-color: var(--el-accent);
            color: var(--el-accent);
            background: #eff6ff;
        }

        /* Quad Spacing Box */
        .el-quad-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr) 28px;
            gap: 4px;
            align-items: center;
        }
        .el-quad-box input {
            width: 100%;
            border: 1px solid #cbd5e1;
            padding: 4px 2px;
            font-size: 11.5px;
            text-align: center;
            outline: none;
        }
        .el-quad-box input:focus {
            border-color: var(--el-accent);
        }
        .el-quad-link {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            color: #64748b;
            font-size: 11px;
            background: #f8fafc;
        }
        .el-quad-link.active {
            background: var(--el-accent);
            color: #ffffff;
            border-color: var(--el-accent);
        }

        .el-color-input-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .el-color-picker-thumb {
            width: 30px;
            height: 30px;
            padding: 0;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* Widgets Grid */
        .el-widgets-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .el-widget-item {
            background: #ffffff;
            border: 1px solid var(--el-border);
            border-radius: 6px !important;
            padding: 12px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
        }
        .el-widget-item:hover {
            border-color: var(--el-accent);
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
            transform: translateY(-1px);
            color: var(--el-accent);
        }
        .el-widget-item i {
            font-size: 18px;
            margin-bottom: 6px;
        }

        /* Live Visual Stage */
        .el-stage {
            flex: 1;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            background: var(--el-canvas-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 12px 180px 12px;
            transition: all 0.25s ease;
            position: relative;
        }
        .el-canvas-paper {
            width: 100%;
            max-width: 1140px;
            min-height: 82vh;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            position: relative;
            padding: 0px;
            box-sizing: border-box;
            overflow: visible;
            transition: max-width 0.25s ease;
        }
        .el-canvas-paper.tablet-view { max-width: 768px !important; }
        .el-canvas-paper.mobile-view { max-width: 375px !important; }

        /* Canvas Nodes */
        .el-node {
            position: relative;
            border: 1px dashed transparent;
            margin-bottom: 0px;
            transition: border-color 0.15s ease;
            max-width: 100%;
            box-sizing: border-box;
        }
        .el-node:hover { border-color: var(--el-pink); }
        .el-node.selected-node { border: 2px solid var(--el-pink) !important; z-index: 25; }

        .el-pink-handle {
            position: absolute;
            top: -13px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--el-pink);
            color: #ffffff;
            border-radius: 5px !important;
            padding: 2px 8px;
            display: none;
            align-items: center;
            gap: 6px;
            z-index: 100;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            font-size: 10px;
        }
        .el-node:hover > .el-pink-handle,
        .el-node.selected-node > .el-pink-handle {
            display: flex;
        }
        .el-pink-handle button {
            background: transparent;
            border: none;
            color: #ffffff;
            padding: 1px 3px;
            cursor: pointer;
        }

        .el-nested-holder {
            min-height: 44px;
            width: 100%;
            border: 1px dashed rgba(15, 23, 42, 0.15);
            position: relative;
            display: flex;
            flex-wrap: wrap;
            box-sizing: border-box;
        }
        .el-inner-plus-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 26px;
            height: 26px;
            border-radius: 5px !important;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 5;
            transition: all 0.15s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .el-inner-plus-btn:hover {
            background: var(--el-accent);
            color: #ffffff;
            border-color: var(--el-accent);
        }
        .el-nested-holder:not(:empty) > .el-inner-plus-btn {
            display: none;
        }

        .el-canvas-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 32px 16px;
            text-align: center;
            margin: 20px auto;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
            width: calc(100% - 32px);
            max-width: 1100px;
        }
        .el-canvas-dropzone:hover, .el-canvas-dropzone.drag-active {
            border-color: var(--el-accent);
            background: #eff6ff;
        }
        .el-drop-circles {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .el-drop-circle {
            width: 34px;
            height: 34px;
            border-radius: 5px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 13px;
        }

        /* Structure Tree Dock */
        .el-structure-tree-dock {
            position: fixed;
            top: 58px;
            right: 16px;
            width: 260px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
            z-index: 1055;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .el-structure-tree-head {
            background: #f8fafc;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--el-border);
        }
        .el-structure-tree-list {
            padding: 6px;
            max-height: 340px;
            overflow-y: auto;
            font-size: 11.5px;
        }
        .el-tree-item {
            padding: 5px 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #334155;
            transition: 0.15s;
            user-select: none;
        }
        .el-tree-item:hover { background: #f1f5f9; }
        .el-tree-item.active { background: #eff6ff; color: var(--el-accent); font-weight: 700; }
        .el-tree-left {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .el-tree-del-btn {
            color: #94a3b8;
            font-size: 11px;
            background: transparent;
            border: none;
            display: none;
            cursor: pointer;
            padding: 2px 4px;
        }
        .el-tree-item:hover .el-tree-del-btn { display: inline-block; }
        .el-tree-del-btn:hover { color: #ef4444; }

        .el-tree-rename-input {
            border: 1px solid var(--el-accent);
            padding: 1px 4px;
            font-size: 11px;
            width: 120px;
            outline: none;
        }

        /* Picker Modal */
        .el-picker-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 12px;
        }
        .el-picker-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.3);
            overflow: hidden;
        }
        .el-picker-head {
            background: #f8fafc;
            padding: 11px 16px;
            border-bottom: 1px solid var(--el-border);
            font-weight: 700;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Toast Alert */
        .sit-toast-alert {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 18px;
            font-size: 12px;
            font-weight: 600;
            display: none;
            z-index: 999999;
            border-left: 4px solid #22c55e;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }

        /* Mobile Backdrop & Floating Inspector Button */
        .el-mobile-backdrop {
            display: none;
            position: fixed;
            top: var(--el-top-h);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 1045;
        }
        .el-mobile-fab {
            display: none;
            position: fixed;
            bottom: 16px;
            left: 16px;
            z-index: 1040;
            background: var(--el-accent);
            color: #ffffff;
            border: none;
            border-radius: 5px !important;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            align-items: center;
            gap: 6px;
        }

        /* Mobile 100/100 Optimizations */
        @media (max-width: 991.98px) {
            .el-sidebar {
                position: fixed;
                top: var(--el-top-h);
                left: 0;
                bottom: 0;
                width: 300px;
                min-width: 300px;
                max-width: 85vw;
                transform: translateX(-100%);
                box-shadow: 6px 0 20px rgba(0, 0, 0, 0.25);
            }
            .el-sidebar.mobile-open {
                transform: translateX(0);
            }
            .el-mobile-backdrop.active {
                display: block;
            }
            .el-mobile-fab {
                display: inline-flex;
            }
            .el-doc-badge {
                max-width: 120px;
            }
            .el-structure-tree-dock {
                display: none;
                right: 10px;
                left: 10px;
                width: auto;
            }
            .el-stage {
                padding: 12px 8px 140px 8px;
            }
        }
    </style>
</head>
<body>

    <!-- Topbar -->
    <div class="el-topbar">
        <div class="el-top-left">
            <button type="button" class="el-top-btn" onclick="window.location.href='{{ $exitRoute }}'" title="Exit Builder">
                <i class="fas fa-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Exit</span>
            </button>
            <button type="button" class="el-top-btn d-lg-none" onclick="toggleMobileInspector()" title="Toggle Inspector Panel">
                <i class="fas fa-sliders-h me-1"></i>
                <span>Panel</span>
            </button>
            <span class="el-doc-badge" title="{{ $item->title ?? 'Untitled Page' }}">
                {{ $item->title ?? 'Untitled Page' }} <span class="text-secondary fw-normal d-none d-xl-inline">(/<span id="topBarSlugDisplay">{{ $item->slug }}</span>)</span>
            </span>
        </div>

        <div class="el-responsive-switch d-none d-md-flex">
            <button type="button" class="el-resp-btn active" onclick="setDeviceView('desktop', this)" title="Desktop View"><i class="fas fa-desktop"></i></button>
            <button type="button" class="el-resp-btn" onclick="setDeviceView('tablet', this)" title="Tablet View"><i class="fas fa-tablet-alt"></i></button>
            <button type="button" class="el-resp-btn" onclick="setDeviceView('mobile', this)" title="Mobile View"><i class="fas fa-mobile-alt"></i></button>
        </div>

        <div class="el-top-right">
            <button type="button" class="el-top-btn" onclick="toggleStructureDock()" title="Structure Tree">
                <i class="fas fa-layer-group"></i>
            </button>
            <a href="{{ $previewUrl }}" id="topPreviewBtn" target="_blank" class="el-top-btn" style="text-decoration:none;">
                <i class="fas fa-eye me-1"></i>
                <span class="d-none d-sm-inline">Preview</span>
            </a>
            <button type="button" class="el-btn-publish-green" onclick="saveElementorVisualLayout()">
                <i class="fas fa-check me-1"></i>
                <span>Publish</span>
            </button>
        </div>
    </div>

    <!-- Mobile Backdrop Overlay -->
    <div class="el-mobile-backdrop" id="mobileSidebarBackdrop" onclick="closeMobileInspector()"></div>

    <!-- Mobile Floating Trigger -->
    <button type="button" class="el-mobile-fab" onclick="toggleMobileInspector()">
        <i class="fas fa-sliders-h"></i>
        <span>Widgets &amp; Style</span>
    </button>

    <!-- Workspace -->
    <div class="el-workspace">
        
        <!-- Left Elementor Dock -->
        <div class="el-sidebar" id="mainBuilderSidebar">
            <div class="el-sidebar-header-title">
                <span id="sidebarHeaderTitle">Elements</span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-light border btn-sm py-0 px-2" style="font-size: 10.5px;" onclick="switchInspectorTab('elements')" title="Show All Widgets">
                        <i class="fas fa-th me-1 text-primary"></i>Widgets
                    </button>
                    <button type="button" class="btn-close btn-sm d-lg-none shadow-none" onclick="closeMobileInspector()"></button>
                </div>
            </div>

            <div class="el-panel-tabs">
                <div class="el-tab-btn" id="tabHeadGeneral" onclick="switchInspectorTab('general')">Content</div>
                <div class="el-tab-btn active" id="tabHeadStyle" onclick="switchInspectorTab('style')">Style</div>
                <div class="el-tab-btn" id="tabHeadInteractions" onclick="switchInspectorTab('interactions')">Advanced</div>
            </div>

            <div class="el-sidebar-body">
                
                <!-- 1. CONTENT / GENERAL TAB -->
                <div id="inspectorTabGeneral" style="display: none;">
                    
                    <div class="el-inspector-section" id="sectionNormalContent">
                        <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                            <span>Content</span> <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="el-inspector-section-body">
                            <div class="el-field-row">
                                <label class="el-field-label">Content / Text / Shortcode</label>
                                <textarea class="el-input-text" rows="5" id="genTextContent" style="font-family: var(--el-font-mono);" oninput="updateActiveContent(this.value)"></textarea>
                                <small class="text-muted d-block mt-1" style="font-size:10px;">Supports <code>[products limit="4"]</code> shortcodes &amp; HTML</small>
                            </div>

                            <div class="el-field-row" id="linkFieldRow">
                                <label class="el-field-label">Link (URL)</label>
                                <input type="text" class="el-input-text font-monospace" id="genLinkUrl" placeholder="https://" oninput="updateSelectedNodeProp('link', this.value)">
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Content Tab for Icon Box -->
                    <div class="el-inspector-section" id="sectionIconBoxContent" style="display:none;">
                        <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                            <span>Icon Box</span> <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="el-inspector-section-body">
                            <div class="el-field-row">
                                <label class="el-field-label">Icon (FontAwesome Class)</label>
                                <input type="text" class="el-input-text font-monospace" id="ibIconInput" placeholder="fas fa-home" oninput="updateIconBoxData('icon', this.value)">
                            </div>
                            <div class="el-field-row">
                                <label class="el-field-label">Title</label>
                                <input type="text" class="el-input-text" id="ibTitleInput" placeholder="hello world" oninput="updateIconBoxData('title', this.value)">
                            </div>
                            <div class="el-field-row">
                                <label class="el-field-label">Description</label>
                                <textarea class="el-input-text" rows="3" id="ibDescInput" oninput="updateIconBoxData('description', this.value)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Dedicated Content Tab for Icon List -->
                    <div class="el-inspector-section" id="sectionIconListContent" style="display:none;">
                        <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                            <span>Icon List</span> <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="el-inspector-section-body">
                            <div class="el-field-row">
                                <label class="el-field-label">Layout</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-1" style="font-size: 11px;" onclick="applySelectedNodeStyle('display', 'block')"><i class="fas fa-list me-1"></i> Default</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-1" style="font-size: 11px;" onclick="applySelectedNodeStyle('display', 'inline-flex')"><i class="fas fa-ellipsis-h me-1"></i> Inline</button>
                                </div>
                            </div>

                            <label class="el-field-label">Items</label>
                            <div id="iconListItemsContainer"></div>
                            
                            <button type="button" class="btn btn-dark btn-sm w-100 mt-2 py-1 fw-semibold" style="font-size: 11.5px;" onclick="addIconListItem()"><i class="fas fa-plus me-1"></i> Add Item</button>
                            
                            <div class="el-field-row mt-3">
                                <label class="el-field-label">Apply Link On</label>
                                <select class="el-select" onchange="updateSelectedNodeProp('linkTarget', this.value)">
                                    <option value="full">Full Width</option>
                                    <option value="inline">Inline</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Section -->
                    <div class="el-inspector-section">
                        <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                            <span>Settings</span> <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="el-inspector-section-body">
                            <div class="el-field-row">
                                <label class="el-field-label">HTML Tag</label>
                                <select class="el-select" id="genHtmlTag" onchange="changeActiveNodeTag(this.value)">
                                    <option value="h1">H1</option>
                                    <option value="h2">H2</option>
                                    <option value="h3">H3</option>
                                    <option value="h4">H4</option>
                                    <option value="p">Paragraph (p)</option>
                                    <option value="div">Div</option>
                                    <option value="section">Section</option>
                                    <option value="a">Link (a)</option>
                                </select>
                            </div>
                            <div class="el-field-row">
                                <label class="el-field-label">Custom Label Name</label>
                                <input type="text" class="el-input-text" id="genCustomName" placeholder=" Hero Section" oninput="updateSelectedNodeProp('label', this.value)">
                            </div>
                            <div class="el-field-row">
                                <label class="el-field-label">CSS ID</label>
                                <input type="text" class="el-input-text font-monospace" id="genCssId" placeholder="custom-id" oninput="updateSelectedNodeProp('id', this.value)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. STYLE TAB -->
                <div id="inspectorTabStyle">
                    <div class="el-inspector-section">
                        <label class="el-field-label">Classes</label>
                        <input type="text" class="el-input-text font-monospace" id="styleCustomClass" placeholder="Type class name" oninput="updateSelectedNodeProp('customClass', this.value)">
                    </div>

                    <!-- Dedicated Style Panels for Icon List -->
                    <div id="styleIconListGroup" style="display:none;">
                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>List</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <div class="el-field-row">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="el-field-label mb-0">Space Between</label>
                                        <span class="small text-muted bn-digit" id="spaceBetweenValDisplay">10px</span>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="60" value="10" oninput="applySelectedNodeStyle('gap', this.value + 'px'); document.getElementById('spaceBetweenValDisplay').innerText = this.value + 'px';">
                                </div>
                                <div class="el-field-row">
                                    <label class="el-field-label">Alignment</label>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-1" onclick="applySelectedNodeStyle('justifyContent', 'flex-start'); applySelectedNodeStyle('textAlign', 'left');"><i class="fas fa-align-left"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-1" onclick="applySelectedNodeStyle('justifyContent', 'center'); applySelectedNodeStyle('textAlign', 'center');"><i class="fas fa-align-center"></i></button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-1" onclick="applySelectedNodeStyle('justifyContent', 'flex-end'); applySelectedNodeStyle('textAlign', 'right');"><i class="fas fa-align-right"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>Icon</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <div class="el-field-row">
                                    <label class="el-field-label">Color</label>
                                    <div class="el-color-input-wrap">
                                        <input type="color" class="el-color-picker-thumb" id="iconColorPicker" oninput="applyIconColor(this.value)">
                                        <input type="text" class="el-input-text font-monospace" id="iconColorInput" placeholder="#e0076a" oninput="applyIconColor(this.value)">
                                    </div>
                                </div>
                                <div class="el-field-row">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="el-field-label mb-0">Size</label>
                                        <span class="small text-muted bn-digit" id="iconSizeValDisplay">14px</span>
                                    </div>
                                    <input type="range" class="form-range" min="8" max="48" value="14" oninput="applyIconSize(this.value); document.getElementById('iconSizeValDisplay').innerText = this.value + 'px';">
                                </div>
                                <div class="el-field-row">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="el-field-label mb-0">Gap</label>
                                        <span class="small text-muted bn-digit" id="iconGapValDisplay">8px</span>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="30" value="8" oninput="applyIconGap(this.value); document.getElementById('iconGapValDisplay').innerText = this.value + 'px';">
                                </div>
                            </div>
                        </div>

                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>Text</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <div class="el-field-row">
                                    <label class="el-field-label">Text Color</label>
                                    <div class="el-color-input-wrap">
                                        <input type="color" class="el-color-picker-thumb" oninput="applySelectedNodeStyle('color', this.value)">
                                        <input type="text" class="el-input-text font-monospace" placeholder="#333333" oninput="applySelectedNodeStyle('color', this.value)">
                                    </div>
                                </div>
                                <div class="el-field-row">
                                    <label class="el-field-label">Font Size</label>
                                    <input type="text" class="el-input-text font-monospace bn-digit" placeholder="14px" oninput="applySelectedNodeStyle('fontSize', formatUnitValue(this.value))">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Style Panels -->
                    <div id="styleGeneralGroup">
                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>Layout &amp; Flexbox</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <div class="el-field-row">
                                    <label class="el-field-label">Display</label>
                                    <select class="el-select" id="styleDisplay" onchange="applySelectedNodeStyle('display', this.value); toggleLayoutOptions(this.value);">
                                        <option value="block">Block</option>
                                        <option value="flex">Flex</option>
                                        <option value="inline-block">Inline Block</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>

                                <div id="flexControlsRow" style="display:none;">
                                    <div class="el-field-row">
                                        <label class="el-field-label">Direction</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-1" onclick="applySelectedNodeStyle('flexDirection', 'row')">→ Row</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-1" onclick="applySelectedNodeStyle('flexDirection', 'column')">↓ Column</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>Spacing</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <label class="el-field-label">Margin (PX / %)</label>
                                <div class="el-quad-box mb-2">
                                    <input type="text" id="mTop" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('margin', 'top', this.value)">
                                    <input type="text" id="mRight" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('margin', 'right', this.value)">
                                    <input type="text" id="mBottom" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('margin', 'bottom', this.value)">
                                    <input type="text" id="mLeft" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('margin', 'left', this.value)">
                                    <div class="el-quad-link active" id="mLinkBtn" onclick="toggleQuadLink('margin')"><i class="fas fa-link"></i></div>
                                </div>

                                <label class="el-field-label">Padding (PX / %)</label>
                                <div class="el-quad-box">
                                    <input type="text" id="pTop" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('padding', 'top', this.value)">
                                    <input type="text" id="pRight" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('padding', 'right', this.value)">
                                    <input type="text" id="pBottom" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('padding', 'bottom', this.value)">
                                    <input type="text" id="pLeft" class="bn-digit" placeholder="0" oninput="applyQuadSpacing('padding', 'left', this.value)">
                                    <div class="el-quad-link active" id="pLinkBtn" onclick="toggleQuadLink('padding')"><i class="fas fa-link"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>Size</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="el-field-label">Width (%)</label>
                                        <input type="text" id="sizeWidth" class="el-input-text bn-digit" placeholder="100%" oninput="applySelectedNodeStyle('width', formatUnitValue(this.value))">
                                    </div>
                                    <div class="col-6">
                                        <label class="el-field-label">Height</label>
                                        <input type="text" id="sizeHeight" class="el-input-text bn-digit" placeholder="auto" oninput="applySelectedNodeStyle('height', formatUnitValue(this.value))">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>Typography</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="el-field-label">Font Size</label>
                                        <input type="text" id="typoFontSize" class="el-input-text bn-digit" placeholder="16px" oninput="applySelectedNodeStyle('fontSize', formatUnitValue(this.value))">
                                    </div>
                                    <div class="col-6">
                                        <label class="el-field-label">Text Align</label>
                                        <select class="el-select" id="styleTextAlignSelect" onchange="applySelectedNodeStyle('textAlign', this.value)">
                                            <option value="left">Left</option>
                                            <option value="center">Center</option>
                                            <option value="right">Right</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="el-field-row">
                                    <label class="el-field-label">Text Color</label>
                                    <div class="el-color-input-wrap">
                                        <input type="color" class="el-color-picker-thumb" id="typoTextColorPicker" oninput="document.getElementById('typoTextColorInput').value = this.value; applySelectedNodeStyle('color', this.value)">
                                        <input type="text" class="el-input-text font-monospace" id="typoTextColorInput" placeholder="#000000" oninput="applySelectedNodeStyle('color', this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="el-inspector-section">
                            <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                                <span>Background</span> <i class="fas fa-chevron-down small text-muted"></i>
                            </div>
                            <div class="el-inspector-section-body">
                                <div class="el-field-row">
                                    <label class="el-field-label">Background Color</label>
                                    <div class="el-color-input-wrap">
                                        <input type="color" class="el-color-picker-thumb" id="bgSolidColorPicker" oninput="document.getElementById('bgSolidColorInput').value = this.value; applySelectedNodeStyle('backgroundColor', this.value)">
                                        <input type="text" class="el-input-text font-monospace" id="bgSolidColorInput" placeholder="#ffffff" oninput="applySelectedNodeStyle('backgroundColor', this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. ADVANCED TAB -->
                <div id="inspectorTabInteractions" style="display: none;">
                    <div class="el-inspector-section">
                        <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                            <span>Layout &amp; Positioning</span> <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="el-inspector-section-body">
                            <div class="el-field-row">
                                <label class="el-field-label">Z-Index</label>
                                <input type="number" class="el-input-text bn-digit" placeholder="1" oninput="applySelectedNodeStyle('zIndex', this.value)">
                            </div>
                            <div class="el-field-row">
                                <label class="el-field-label">Sticky Position</label>
                                <select class="el-select" onchange="applySelectedNodeStyle('position', this.value === 'top' ? 'sticky' : 'static'); applySelectedNodeStyle('top', '0');">
                                    <option value="none">None</option>
                                    <option value="top">Stick to Top</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="el-inspector-section">
                        <div class="el-inspector-section-header" onclick="toggleInspectorAccordion(this)">
                            <span>Responsive Visibility</span> <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="el-inspector-section-body">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="hideDesktopSwitch" onchange="toggleResponsiveVisibility('desktop', this.checked)" style="cursor: pointer;">
                                <label class="form-check-label small fw-semibold" for="hideDesktopSwitch" style="font-size: 12px; cursor: pointer;">Hide On Desktop</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="hideTabletSwitch" onchange="toggleResponsiveVisibility('tablet', this.checked)" style="cursor: pointer;">
                                <label class="form-check-label small fw-semibold" for="hideTabletSwitch" style="font-size: 12px; cursor: pointer;">Hide On Tablet</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="hideMobileSwitch" onchange="toggleResponsiveVisibility('mobile', this.checked)" style="cursor: pointer;">
                                <label class="form-check-label small fw-semibold" for="hideMobileSwitch" style="font-size: 12px; cursor: pointer;">Hide On Mobile</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Elements Palette -->
                <div id="inspectorElementsPalette" style="display: none;">
                    <div class="el-widgets-grid">
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'container')" onclick="insertNewWidget('container', null)"><i class="far fa-square text-primary"></i><span>Div block</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'heading')" onclick="insertNewWidget('heading', null)"><i class="fas fa-heading text-dark"></i><span>Heading</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'paragraph')" onclick="insertNewWidget('paragraph', null)"><i class="fas fa-paragraph text-secondary"></i><span>Paragraph</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'icon_box')" onclick="insertNewWidget('icon_box', null)"><i class="fas fa-icons text-warning"></i><span>Icon Box</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'icon_list')" onclick="insertNewWidget('icon_list', null)"><i class="fas fa-list-ul text-info"></i><span>Icon List</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'button')" onclick="insertNewWidget('button', null)"><i class="fas fa-mouse-pointer text-warning"></i><span>Button</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'image')" onclick="insertNewWidget('image', null)"><i class="fas fa-image text-success"></i><span>Image</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'custom_html')" onclick="insertNewWidget('custom_html', null)"><i class="fas fa-code text-info"></i><span>HTML / Shortcode</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'youtube')" onclick="insertNewWidget('youtube', null)"><i class="fab fa-youtube text-danger"></i><span>YouTube</span></div>
                        <div class="el-widget-item" draggable="true" ondragstart="startDragWidget(event, 'products_grid')" onclick="insertNewWidget('products_grid', null)"><i class="fas fa-shopping-bag text-danger"></i><span>Products Grid</span></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Live Visual Stage -->
        <div class="el-stage">
            <div class="el-canvas-paper" id="canvasPaper" ondragover="handleDragOverCanvas(event)" ondrop="handleDropCanvas(event, null)">
                <div id="elRenderCanvasNodes"></div>

                <div class="el-canvas-dropzone" onclick="openPickerModal(null)">
                    <div class="el-drop-circles">
                        <div class="el-drop-circle" style="background: var(--el-accent);"><i class="fas fa-plus"></i></div>
                        <div class="el-drop-circle" style="background: #1e293b;"><i class="fas fa-folder"></i></div>
                    </div>
                    <div class="fw-bold mt-2 text-dark" style="font-size:13px;">Add Section / Elements</div>
                    <small class="text-muted" style="font-size:11px;">Click to choose from widget elements</small>
                </div>
            </div>
        </div>

        <!-- Structure Tree Dock -->
        <div class="el-structure-tree-dock" id="structureTreeDock">
            <div class="el-structure-tree-head">
                <span>Structure</span>
                <i class="fas fa-times text-muted" style="cursor:pointer;" onclick="toggleStructureDock()"></i>
            </div>
            <div class="el-structure-tree-list" id="structureTreeList"></div>
        </div>

    </div>

    <!-- Element Picker Modal -->
    <div class="el-picker-modal" id="elementPickerModal" onclick="closePickerModal(event)">
        <div class="el-picker-card" onclick="event.stopPropagation()">
            <div class="el-picker-head">
                <span>Choose Element to Insert</span>
                <button type="button" class="btn-close btn-sm shadow-none" onclick="closePickerModal()"></button>
            </div>
            <div class="p-3">
                <div class="el-widgets-grid">
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('container')"><i class="far fa-square text-primary"></i><span>Div Block</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('heading')"><i class="fas fa-heading text-dark"></i><span>Heading</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('paragraph')"><i class="fas fa-paragraph text-secondary"></i><span>Paragraph</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('icon_box')"><i class="fas fa-icons text-warning"></i><span>Icon Box</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('icon_list')"><i class="fas fa-list-ul text-info"></i><span>Icon List</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('button')"><i class="fas fa-mouse-pointer text-warning"></i><span>Button</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('image')"><i class="fas fa-image text-success"></i><span>Image</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('custom_html')"><i class="fas fa-code text-info"></i><span>HTML / Shortcode</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('youtube')"><i class="fab fa-youtube text-danger"></i><span>YouTube</span></div>
                    <div class="el-widget-item" onclick="confirmInsertFromPicker('products_grid')"><i class="fas fa-shopping-bag text-danger"></i><span>Products Grid</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Icon Class Picker Modal (Replaces raw prompt()) -->
    <div class="modal fade" id="iconPromptModal" tabindex="-1" aria-hidden="true" style="z-index: 2050;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg p-3" style="border-radius: 8px !important;">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13.5px;">Select or Enter Icon Class</h6>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <input type="text" id="modalIconClassInput" class="el-input-text font-monospace mb-2" placeholder=" fas fa-check">
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <button type="button" class="btn btn-light border btn-sm p-1 px-2" onclick="setQuickIcon('fas fa-check')"><i class="fas fa-check text-success"></i></button>
                    <button type="button" class="btn btn-light border btn-sm p-1 px-2" onclick="setQuickIcon('fas fa-star')"><i class="fas fa-star text-warning"></i></button>
                    <button type="button" class="btn btn-light border btn-sm p-1 px-2" onclick="setQuickIcon('fas fa-arrow-right')"><i class="fas fa-arrow-right text-primary"></i></button>
                    <button type="button" class="btn btn-light border btn-sm p-1 px-2" onclick="setQuickIcon('fas fa-dot-circle')"><i class="fas fa-dot-circle text-info"></i></button>
                    <button type="button" class="btn btn-light border btn-sm p-1 px-2" onclick="setQuickIcon('fas fa-shield-alt')"><i class="fas fa-shield-alt text-secondary"></i></button>
                    <button type="button" class="btn btn-light border btn-sm p-1 px-2" onclick="setQuickIcon('fas fa-times')"><i class="fas fa-times text-danger"></i></button>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border btn-sm px-3 py-1 fw-semibold" style="font-size: 12px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-3 py-1 fw-semibold" style="font-size: 12px;" onclick="confirmIconPrompt()">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notice Modal (Replaces raw alert()) -->
    <div class="modal fade" id="builderNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 2060;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
                <div class="modal-body p-1">
                    <div class="mb-2 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Notice</h6>
                    <p class="text-muted small mb-3" id="builderNoticeMsg" style="font-size: 12px;"></p>
                    <button type="button" class="btn btn-dark btn-sm px-4 py-1 fw-semibold" style="font-size: 12px;" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Toast -->
    <div class="sit-toast-alert" id="sitToastAlert">Layout updated successfully!</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let liveCanvasBlocks = @json($blocks ?? []);
        let selectedNodeId = null;
        let activeDraggedType = null;
        let pickerTargetParentId = null;
        let treeDraggedNodeId = null;
        let pendingIconEditIdx = null;
        const apiSaveEndpoint = "{{ $saveRoute }}";
        const quadLinkedState = { margin: true, padding: true };

        function toggleMobileInspector() {
            const sb = document.getElementById('mainBuilderSidebar');
            const bd = document.getElementById('mobileSidebarBackdrop');
            sb.classList.toggle('mobile-open');
            bd.classList.toggle('active');
        }

        function closeMobileInspector() {
            document.getElementById('mainBuilderSidebar').classList.remove('mobile-open');
            document.getElementById('mobileSidebarBackdrop').classList.remove('active');
        }

        function showBuilderNotice(msg) {
            document.getElementById('builderNoticeMsg').innerText = msg;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('builderNoticeModal')).show();
        }

        function sanitizeTree(nodes) {
            if (!Array.isArray(nodes)) return [];
            nodes.forEach(n => {
                if (!n.id) n.id = 'node_' + Math.random().toString(36).substr(2, 9);
                if (!n.children) n.children = [];
                if (!n.style) n.style = {};
                if (!n.style.margin) n.style.margin = '0px';
                if (!n.style.padding) n.style.padding = '0px';
                if (!n.style.boxSizing) n.style.boxSizing = 'border-box';
                if (n.children.length > 0) sanitizeTree(n.children);
            });
            return nodes;
        }

        document.addEventListener('DOMContentLoaded', () => {
            liveCanvasBlocks = sanitizeTree(liveCanvasBlocks);
            renderLiveCanvasDOM();
            renderStructureTree();
            if (liveCanvasBlocks.length > 0) {
                selectCanvasNode(liveCanvasBlocks[0].id);
            } else {
                switchInspectorTab('elements');
            }
        });

        function toggleInspectorAccordion(headerEl) {
            const section = headerEl.closest('.el-inspector-section');
            if (section) section.classList.toggle('collapsed');
        }

        function formatUnitValue(val) {
            if (!val || val === 'auto') return val;
            val = String(val).trim();
            if (/^-?\d+(\.\d+)?$/.test(val)) return val + 'px';
            return val;
        }

        function setDeviceView(mode, btn) {
            document.querySelectorAll('.el-resp-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const paper = document.getElementById('canvasPaper');
            paper.classList.remove('tablet-view', 'mobile-view');
            if (mode === 'tablet') paper.classList.add('tablet-view');
            if (mode === 'mobile') paper.classList.add('mobile-view');
        }

        function toggleLayoutOptions(val) {
            document.getElementById('flexControlsRow').style.display = (val === 'flex') ? 'block' : 'none';
        }

        function switchInspectorTab(tab) {
            document.querySelectorAll('.el-tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('inspectorTabGeneral').style.display = 'none';
            document.getElementById('inspectorTabStyle').style.display = 'none';
            document.getElementById('inspectorTabInteractions').style.display = 'none';
            document.getElementById('inspectorElementsPalette').style.display = 'none';

            if (tab === 'elements') {
                document.getElementById('sidebarHeaderTitle').innerText = 'Elements';
                document.getElementById('inspectorElementsPalette').style.display = 'block';
            } else if (tab === 'general') {
                document.getElementById('tabHeadGeneral').classList.add('active');
                document.getElementById('sidebarHeaderTitle').innerText = 'Edit ' + getActiveBlockTitle();
                document.getElementById('inspectorTabGeneral').style.display = 'block';
            } else if (tab === 'style') {
                document.getElementById('tabHeadStyle').classList.add('active');
                document.getElementById('sidebarHeaderTitle').innerText = 'Edit ' + getActiveBlockTitle();
                document.getElementById('inspectorTabStyle').style.display = 'block';
            } else if (tab === 'interactions') {
                document.getElementById('tabHeadInteractions').classList.add('active');
                document.getElementById('sidebarHeaderTitle').innerText = 'Edit ' + getActiveBlockTitle();
                document.getElementById('inspectorTabInteractions').style.display = 'block';
            }
        }

        function getActiveBlockTitle() {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (node) return (node.data?.label || node.data?.tag || node.type || 'Element');
            return 'Element';
        }

        function toggleStructureDock() {
            const dock = document.getElementById('structureTreeDock');
            const currentDisplay = window.getComputedStyle(dock).display;
            dock.style.display = (currentDisplay === 'none') ? 'flex' : 'none';
        }

        function openPickerModal(parentId) {
            pickerTargetParentId = parentId;
            document.getElementById('elementPickerModal').style.display = 'flex';
        }

        function closePickerModal() {
            document.getElementById('elementPickerModal').style.display = 'none';
        }

        function confirmInsertFromPicker(type) {
            closePickerModal();
            insertNewWidget(type, pickerTargetParentId);
        }

        function startDragWidget(e, type) {
            activeDraggedType = type;
            e.dataTransfer.setData('text/plain', type);
        }
        function handleDragOverCanvas(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        function handleDropCanvas(e, parentId) {
            e.preventDefault();
            e.stopPropagation();
            const type = e.dataTransfer.getData('text/plain') || activeDraggedType;
            if (type) {
                insertNewWidget(type, parentId);
            }
        }

        function findNodeById(nodes, id) {
            for (let n of nodes) {
                if (n.id === id) return n;
                if (n.children && n.children.length > 0) {
                    let found = findNodeById(n.children, id);
                    if (found) return found;
                }
            }
            return null;
        }

        function findParentAndIndex(nodes, id) {
            for (let i = 0; i < nodes.length; i++) {
                if (nodes[i].id === id) return { parent: nodes, index: i };
                if (nodes[i].children && nodes[i].children.length > 0) {
                    let res = findParentAndIndex(nodes[i].children, id);
                    if (res) return res;
                }
            }
            return null;
        }

        function insertNewWidget(type, parentId) {
            let data = {};
            let style = {
                margin: '0px',
                padding: '0px',
                boxSizing: 'border-box',
                maxWidth: '100%'
            };

            if (type === 'container') {
                data = { tag: 'div', label: 'div' };
                style.minHeight = '60px';
                style.width = '100%';
                style.display = 'flex';
                style.flexDirection = 'row';
                style.flexWrap = 'wrap';
            } else if (type === 'heading') {
                data = { tag: 'h2', text: 'This is a title', label: 'Heading' };
                style.color = '#0f172a';
                style.fontSize = '24px';
                style.fontWeight = '700';
                style.textAlign = 'left';
            } else if (type === 'paragraph') {
                data = { tag: 'p', text: 'Type your paragraph here', label: 'Paragraph' };
                style.color = '#334155';
                style.fontSize = '15px';
                style.textAlign = 'left';
            } else if (type === 'icon_box') {
                data = {
                    tag: 'div',
                    icon: 'fas fa-home',
                    title: 'hello world',
                    description: 'This is an icon box description.',
                    label: 'Icon Box'
                };
                style.textAlign = 'center';
                style.color = '#334155';
            } else if (type === 'icon_list') {
                data = {
                    tag: 'div',
                    label: 'Icon List',
                    items: [
                        { text: 'List Item #1', icon: 'fas fa-check', link: '#' },
                        { text: 'List Item #2', icon: 'fas fa-times', link: '#' },
                        { text: 'List Item #3', icon: 'fas fa-dot-circle', link: '#' }
                    ],
                    iconColor: '#e0076a',
                    iconSize: '14px',
                    iconGap: '8px'
                };
                style.fontSize = '14px';
                style.color = '#334155';
                style.display = 'block';
            } else if (type === 'button') {
                data = { tag: 'a', text: 'Button Text', link: '/shop', label: 'Button' };
                style.backgroundColor = '#2563eb';
                style.color = '#ffffff';
                style.borderRadius = '5px';
                style.padding = '8px 20px';
                style.display = 'inline-block';
                style.textAlign = 'left';
            } else if (type === 'image') {
                data = { tag: 'img', src: 'https://placehold.co/800x400', label: 'Image' };
                style.width = '100%';
            } else if (type === 'custom_html') {
                data = { tag: 'div', html: '<div>[products limit="4"]</div>', label: 'HTML Block' };
                style.width = '100%';
            } else if (type === 'youtube') {
                data = { tag: 'iframe', url: 'https://www.youtube.com/embed/dQw4w9WgXcQ', label: 'Video' };
            } else if (type === 'products_grid') {
                data = { tag: 'section', title: 'Featured Products', limit: 8, label: 'Products' };
            }

            const newNode = {
                id: 'node_' + Math.random().toString(36).substr(2, 9),
                type: type,
                data: data,
                style: style,
                children: []
            };

            if (parentId) {
                const p = findNodeById(liveCanvasBlocks, parentId);
                if (p) {
                    if (!p.children) p.children = [];
                    p.children.push(newNode);
                } else {
                    liveCanvasBlocks.push(newNode);
                }
            } else {
                liveCanvasBlocks.push(newNode);
            }

            renderLiveCanvasDOM();
            renderStructureTree();
            selectCanvasNode(newNode.id);
        }

        function renderLiveCanvasDOM() {
            const paper = document.getElementById('elRenderCanvasNodes');
            paper.innerHTML = '';
            liveCanvasBlocks.forEach(node => {
                paper.appendChild(createNodeElement(node));
            });
        }

        function createNodeElement(node) {
            const el = document.createElement('div');
            el.className = 'el-node' + (selectedNodeId === node.id ? ' selected-node' : '');
            el.id = 'live-dom-' + node.id;

            if (node.style && node.style.width) el.style.width = node.style.width;
            if (node.style && node.style.textAlign) el.style.textAlign = node.style.textAlign;

            el.onclick = (e) => {
                e.stopPropagation();
                selectCanvasNode(node.id);
            };

            const handle = document.createElement('div');
            handle.className = 'el-pink-handle';
            handle.innerHTML = `
                <button type="button" onclick="event.stopPropagation(); openPickerModal('${node.id}')" title="Insert Element"><i class="fas fa-plus"></i></button>
                <button type="button" onclick="event.stopPropagation(); selectCanvasNode('${node.id}'); if(window.innerWidth < 992) toggleMobileInspector();" title="Edit"><i class="fas fa-ellipsis-v"></i></button>
                <button type="button" onclick="event.stopPropagation(); duplicateNode('${node.id}')" title="Duplicate"><i class="far fa-copy"></i></button>
                <button type="button" onclick="event.stopPropagation(); deleteNode('${node.id}')" title="Delete"><i class="fas fa-times"></i></button>
            `;
            el.appendChild(handle);

            const slot = document.createElement('div');
            slot.id = 'elem-target-' + node.id;
            applyStylesToElement(slot, node.style);

            if (node.type === 'container') {
                const holder = document.createElement('div');
                holder.className = 'el-nested-holder';
                holder.ondragover = (e) => { e.preventDefault(); e.stopPropagation(); holder.style.borderColor = 'var(--el-pink)'; };
                holder.ondragleave = (e) => { e.preventDefault(); holder.style.borderColor = 'rgba(15, 23, 42, 0.15)'; };
                holder.ondrop = (e) => { handleDropCanvas(e, node.id); };

                const innerPlus = document.createElement('div');
                innerPlus.className = 'el-inner-plus-btn';
                innerPlus.innerHTML = '<i class="fas fa-plus"></i>';
                innerPlus.title = "Add element to this block";
                innerPlus.onclick = (e) => {
                    e.stopPropagation();
                    openPickerModal(node.id);
                };
                holder.appendChild(innerPlus);

                if (node.children && node.children.length > 0) {
                    node.children.forEach(child => {
                        holder.appendChild(createNodeElement(child));
                    });
                }
                slot.appendChild(holder);
            } else if (node.type === 'heading') {
                const h = document.createElement(node.data?.tag || 'h2');
                h.id = 'inner-text-' + node.id;
                h.contentEditable = "true";
                h.style.outline = "none";
                h.style.margin = "0";
                h.style.width = "100%";
                h.style.display = "block";
                applyStylesToElement(h, node.style);
                h.innerHTML = node.data?.text || 'Heading';
                h.onblur = (e) => { 
                    node.data.text = e.target.innerHTML;
                    const txtArea = document.getElementById('genTextContent');
                    if (txtArea && selectedNodeId === node.id) txtArea.value = node.data.text;
                };
                slot.appendChild(h);
            } else if (node.type === 'paragraph') {
                const p = document.createElement('p');
                p.id = 'inner-text-' + node.id;
                p.contentEditable = "true";
                p.style.outline = "none";
                p.style.margin = "0";
                p.style.width = "100%";
                p.style.display = "block";
                applyStylesToElement(p, node.style);
                p.innerHTML = node.data?.text || 'Type your paragraph here';
                p.onblur = (e) => { 
                    node.data.text = e.target.innerHTML;
                    const txtArea = document.getElementById('genTextContent');
                    if (txtArea && selectedNodeId === node.id) txtArea.value = node.data.text;
                };
                slot.appendChild(p);
            } else if (node.type === 'icon_box') {
                const d = node.data || {};
                slot.innerHTML = `
                    <div style="font-size:38px; color:var(--el-pink); margin-bottom:8px;"><i class="${d.icon || 'fas fa-home'}"></i></div>
                    <h4 style="font-weight:700; margin-bottom:6px;">${d.title || 'hello world'}</h4>
                    <p style="font-size:14px; opacity:0.85; margin:0;">${d.description || ''}</p>
                `;
            } else if (node.type === 'icon_list') {
                const items = node.data?.items || [];
                const icColor = node.data?.iconColor || 'var(--el-pink)';
                const icSize = node.data?.iconSize || '14px';
                const icGap = node.data?.iconGap || '8px';
                let listHtml = `<ul style="list-style:none; padding:0; margin:0; display:${node.style?.display || 'block'}; gap:${node.style?.gap || '10px'};">`;
                items.forEach(it => {
                    listHtml += `<li style="display:flex; align-items:center; gap:${icGap}; margin-bottom:6px;"><i class="${it.icon || 'fas fa-check'}" style="color:${icColor}; font-size:${icSize};"></i><span>${it.text}</span></li>`;
                });
                listHtml += '</ul>';
                slot.innerHTML = listHtml;
            } else if (node.type === 'button') {
                const a = document.createElement('a');
                a.id = 'inner-text-' + node.id;
                a.href = node.data?.link || '#';
                a.style.textDecoration = 'none';
                applyStylesToElement(a, node.style);
                a.innerText = node.data?.text || 'Button';
                slot.appendChild(a);
            } else if (node.type === 'image') {
                const img = document.createElement('img');
                img.src = node.data?.src || 'https://placehold.co/800x400';
                img.style.maxWidth = '100%';
                applyStylesToElement(img, node.style);
                slot.appendChild(img);
            } else if (node.type === 'custom_html') {
                slot.id = 'elem-target-' + node.id;
                const rawCode = node.data?.html || (node.data?.text || '<div>[products limit="4"]</div>');
                slot.innerHTML = rawCode;
            } else if (node.type === 'youtube') {
                slot.className = 'ratio ratio-16x9 rounded overflow-hidden';
                slot.innerHTML = `<iframe src="${node.data?.url}" allowfullscreen></iframe>`;
            } else {
                slot.innerHTML = `<div class="p-3 bg-light border text-center small">${node.type.toUpperCase()}</div>`;
            }

            el.appendChild(slot);
            return el;
        }

        function applyStylesToElement(el, s) {
            if (!s) return;
            for (let prop in s) {
                if (s[prop] !== '' && s[prop] !== undefined) {
                    el.style[prop] = s[prop];
                }
            }
        }

        function updateActiveContent(val) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node) return;
            if (!node.data) node.data = {};
            node.data.text = val;
            node.data.html = val;

            const targetEl = document.getElementById('elem-target-' + node.id);
            if (targetEl) targetEl.innerHTML = val;
        }

        function updateIconBoxData(key, val) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node) return;
            if (!node.data) node.data = {};
            node.data[key] = val;
            renderLiveCanvasDOM();
        }

        function selectCanvasNode(id) {
            selectedNodeId = id;

            document.querySelectorAll('.el-node').forEach(el => el.classList.remove('selected-node'));
            const activeDOM = document.getElementById('live-dom-' + id);
            if (activeDOM) {
                activeDOM.classList.add('selected-node');
                activeDOM.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            renderStructureTree();

            const node = findNodeById(liveCanvasBlocks, id);
            if (!node) return;

            document.getElementById('sectionNormalContent').style.display = (node.type === 'icon_box' || node.type === 'icon_list') ? 'none' : 'block';
            document.getElementById('sectionIconBoxContent').style.display = (node.type === 'icon_box') ? 'block' : 'none';
            document.getElementById('sectionIconListContent').style.display = (node.type === 'icon_list') ? 'block' : 'none';

            document.getElementById('styleIconListGroup').style.display = (node.type === 'icon_list') ? 'block' : 'none';
            document.getElementById('styleGeneralGroup').style.display = (node.type === 'icon_list') ? 'none' : 'block';

            if (node.type === 'icon_box') {
                document.getElementById('ibIconInput').value = node.data?.icon || 'fas fa-home';
                document.getElementById('ibTitleInput').value = node.data?.title || '';
                document.getElementById('ibDescInput').value = node.data?.description || '';
            } else if (node.type === 'icon_list') {
                renderIconListRepeater(node.data?.items || []);
            } else {
                document.getElementById('genTextContent').value = node.data?.html || (node.data?.text || '');
            }

            document.getElementById('genHtmlTag').value = node.data?.tag || 'div';
            document.getElementById('genCustomName').value = node.data?.label || '';
            document.getElementById('genLinkUrl').value = node.data?.link || '';
            document.getElementById('genCssId').value = node.data?.id || '';

            const s = node.style || {};
            document.getElementById('styleDisplay').value = s.display || 'block';
            toggleLayoutOptions(s.display || 'block');

            document.getElementById('styleCustomClass').value = node.data?.customClass || '';
            document.getElementById('sizeWidth').value = s.width || '';
            document.getElementById('sizeHeight').value = s.height || '';
            document.getElementById('typoFontSize').value = s.fontSize || '';
            document.getElementById('styleTextAlignSelect').value = s.textAlign || 'left';
            document.getElementById('typoTextColorInput').value = s.color || '';
            document.getElementById('bgSolidColorInput').value = s.backgroundColor || '';

            populateQuadInputs('margin', s.margin);
            populateQuadInputs('padding', s.padding);

            switchInspectorTab('general');
        }

        function renderIconListRepeater(items) {
            const container = document.getElementById('iconListItemsContainer');
            container.innerHTML = '';

            items.forEach((it, idx) => {
                const box = document.createElement('div');
                box.className = 'el-repeater-box';
                box.innerHTML = `
                    <div class="el-repeater-head" onclick="toggleRepeaterBody(${idx})">
                        <span class="text-truncate pe-2"><i class="${it.icon || 'fas fa-check'} me-2 text-primary"></i>${it.text || 'Item'}</span>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <button type="button" class="btn btn-sm btn-link p-0 text-secondary" onclick="event.stopPropagation(); duplicateIconItem(${idx})" title="Duplicate"><i class="far fa-copy"></i></button>
                            <button type="button" class="btn btn-sm btn-link p-0 text-danger" onclick="event.stopPropagation(); removeIconItem(${idx})" title="Remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="el-repeater-body" id="rep-body-${idx}" style="display:${idx === 0 ? 'block' : 'none'};">
                        <div class="el-field-row">
                            <label class="el-field-label">Text</label>
                            <input type="text" class="el-input-text" value="${it.text || ''}" oninput="updateIconListItem(${idx}, 'text', this.value)">
                        </div>
                        <div class="el-field-row">
                            <label class="el-field-label">Icon</label>
                            <div class="el-icon-selector-frame" onclick="promptIconSelect(${idx})">
                                <i class="${it.icon || 'fas fa-check'}"></i>
                            </div>
                            <input type="text" class="el-input-text font-monospace" value="${it.icon || 'fas fa-check'}" placeholder=" fas fa-check" oninput="updateIconListItem(${idx}, 'icon', this.value)">
                        </div>
                        <div class="el-field-row">
                            <label class="el-field-label">Link</label>
                            <input type="text" class="el-input-text font-monospace" value="${it.link || '#'}" oninput="updateIconListItem(${idx}, 'link', this.value)">
                        </div>
                    </div>
                `;
                container.appendChild(box);
            });
        }

        function promptIconSelect(idx) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node || !node.data.items) return;
            pendingIconEditIdx = idx;
            document.getElementById('modalIconClassInput').value = node.data.items[idx].icon || 'fas fa-check';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('iconPromptModal')).show();
        }

        function setQuickIcon(cls) {
            document.getElementById('modalIconClassInput').value = cls;
        }

        function confirmIconPrompt() {
            const val = document.getElementById('modalIconClassInput').value.trim();
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (val && node && node.data.items && pendingIconEditIdx !== null) {
                updateIconListItem(pendingIconEditIdx, 'icon', val);
                renderIconListRepeater(node.data.items);
            }
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('iconPromptModal'));
            if (modalInstance) modalInstance.hide();
        }

        function toggleRepeaterBody(idx) {
            const body = document.getElementById('rep-body-' + idx);
            if (body) body.style.display = (body.style.display === 'none') ? 'block' : 'none';
        }

        function addIconListItem() {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node) return;
            if (!node.data.items) node.data.items = [];
            node.data.items.push({ text: 'List Item #' + (node.data.items.length + 1), icon: 'fas fa-check', link: '#' });
            renderIconListRepeater(node.data.items);
            renderLiveCanvasDOM();
        }

        function duplicateIconItem(idx) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node || !node.data.items) return;
            const copy = JSON.parse(JSON.stringify(node.data.items[idx]));
            node.data.items.splice(idx + 1, 0, copy);
            renderIconListRepeater(node.data.items);
            renderLiveCanvasDOM();
        }

        function removeIconItem(idx) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node || !node.data.items) return;
            node.data.items.splice(idx, 1);
            renderIconListRepeater(node.data.items);
            renderLiveCanvasDOM();
        }

        function updateIconListItem(idx, key, val) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node || !node.data.items) return;
            node.data.items[idx][key] = val;
            renderLiveCanvasDOM();
        }

        function applyIconColor(color) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node) return;
            if (!node.data) node.data = {};
            node.data.iconColor = color;
            document.getElementById('iconColorInput').value = color;
            renderLiveCanvasDOM();
        }

        function applyIconSize(size) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node) return;
            if (!node.data) node.data = {};
            node.data.iconSize = size + 'px';
            renderLiveCanvasDOM();
        }

        function applyIconGap(gap) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node) return;
            if (!node.data) node.data = {};
            node.data.iconGap = gap + 'px';
            renderLiveCanvasDOM();
        }

        function changeActiveNodeTag(newTag) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (node) {
                if (!node.data) node.data = {};
                node.data.tag = newTag;
                renderLiveCanvasDOM();
                renderStructureTree();
            }
        }

        function toggleResponsiveVisibility(device, checked) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (!node) return;
            if (!node.data) node.data = {};
            if (!node.data.responsiveHide) node.data.responsiveHide = {};
            node.data.responsiveHide[device] = checked;
        }

        function populateQuadInputs(type, valStr) {
            const prefix = (type === 'margin') ? 'm' : 'p';
            if (!valStr) {
                document.getElementById(prefix + 'Top').value = '';
                document.getElementById(prefix + 'Right').value = '';
                document.getElementById(prefix + 'Bottom').value = '';
                document.getElementById(prefix + 'Left').value = '';
                return;
            }
            const parts = String(valStr).trim().split(/\s+/);
            if (parts.length === 1) {
                const val = parts[0].replace('px', '');
                document.getElementById(prefix + 'Top').value = val;
                document.getElementById(prefix + 'Right').value = val;
                document.getElementById(prefix + 'Bottom').value = val;
                document.getElementById(prefix + 'Left').value = val;
            } else if (parts.length === 4) {
                document.getElementById(prefix + 'Top').value = parts[0].replace('px', '');
                document.getElementById(prefix + 'Right').value = parts[1].replace('px', '');
                document.getElementById(prefix + 'Bottom').value = parts[2].replace('px', '');
                document.getElementById(prefix + 'Left').value = parts[3].replace('px', '');
            }
        }

        function updateSelectedNodeProp(prop, val) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (node) {
                if (!node.data) node.data = {};
                node.data[prop] = val;
                renderLiveCanvasDOM();
                renderStructureTree();
            }
        }

        function applySelectedNodeStyle(prop, val) {
            const node = findNodeById(liveCanvasBlocks, selectedNodeId);
            if (node) {
                if (!node.style) node.style = {};
                node.style[prop] = val;

                if (prop === 'width') {
                    const domWrap = document.getElementById('live-dom-' + node.id);
                    if (domWrap) domWrap.style.width = val;
                }
                if (prop === 'textAlign') {
                    const domWrap = document.getElementById('live-dom-' + node.id);
                    if (domWrap) domWrap.style.textAlign = val;
                }

                const targetEl = document.getElementById('elem-target-' + node.id);
                if (targetEl) targetEl.style[prop] = val;

                const innerTxt = document.getElementById('inner-text-' + node.id);
                if (innerTxt) innerTxt.style[prop] = val;
            }
        }

        function toggleQuadLink(type) {
            quadLinkedState[type] = !quadLinkedState[type];
            const btn = document.getElementById(type === 'margin' ? 'mLinkBtn' : 'pLinkBtn');
            if (btn) btn.classList.toggle('active', quadLinkedState[type]);
        }

        function applyQuadSpacing(type, side, val) {
            const prefix = (type === 'margin') ? 'm' : 'p';
            val = formatUnitValue(val);

            if (quadLinkedState[type]) {
                document.getElementById(prefix + 'Top').value = val;
                document.getElementById(prefix + 'Right').value = val;
                document.getElementById(prefix + 'Bottom').value = val;
                document.getElementById(prefix + 'Left').value = val;

                applySelectedNodeStyle(type, `${val} ${val} ${val} ${val}`);
            } else {
                const t = formatUnitValue(document.getElementById(prefix + 'Top').value) || '0px';
                const r = formatUnitValue(document.getElementById(prefix + 'Right').value) || '0px';
                const b = formatUnitValue(document.getElementById(prefix + 'Bottom').value) || '0px';
                const l = formatUnitValue(document.getElementById(prefix + 'Left').value) || '0px';

                applySelectedNodeStyle(type, `${t} ${r} ${b} ${l}`);
            }
        }

        function duplicateNode(id) {
            const res = findParentAndIndex(liveCanvasBlocks, id);
            if (res) {
                const copy = JSON.parse(JSON.stringify(res.parent[res.index]));
                sanitizeTree([copy]);
                res.parent.splice(res.index + 1, 0, copy);
                renderLiveCanvasDOM();
                renderStructureTree();
                selectCanvasNode(copy.id);
            }
        }

        function deleteNode(id) {
            const res = findParentAndIndex(liveCanvasBlocks, id);
            if (res) {
                res.parent.splice(res.index, 1);
                selectedNodeId = null;
                renderLiveCanvasDOM();
                renderStructureTree();
                switchInspectorTab('elements');
            }
        }

        function renderStructureTree() {
            const list = document.getElementById('structureTreeList');
            list.innerHTML = '';
            if (liveCanvasBlocks.length === 0) {
                list.innerHTML = '<div class="text-muted text-center py-3">Empty</div>';
                return;
            }
            renderTreeNodes(liveCanvasBlocks, list, 0);
        }

        function renderTreeNodes(nodes, container, depth) {
            nodes.forEach(node => {
                const item = document.createElement('div');
                item.className = 'el-tree-item' + (selectedNodeId === node.id ? ' active' : '');
                item.style.paddingLeft = (depth * 14 + 6) + 'px';
                item.draggable = true;

                item.onclick = (e) => {
                    e.stopPropagation();
                    selectCanvasNode(node.id);
                };

                let icon = 'fa-square';
                if (node.type === 'heading') icon = 'fa-heading';
                if (node.type === 'paragraph') icon = 'fa-paragraph';
                if (node.type === 'icon_box') icon = 'fa-icons';
                if (node.type === 'icon_list') icon = 'fa-list-ul';
                if (node.type === 'button') icon = 'fa-mouse-pointer';
                if (node.type === 'custom_html') icon = 'fa-code';

                const hasKids = node.children && node.children.length > 0;
                const caret = hasKids ? (node._treeCollapsed ? 'fa-chevron-right' : 'fa-chevron-down') : 'fa-caret-right';

                item.innerHTML = `
                    <div class="el-tree-left">
                        <i class="fas ${caret} text-muted tree-caret" style="font-size:9px; cursor:pointer;" onclick="toggleTreeCollapse(event, '${node.id}')"></i>
                        <i class="fas ${icon} text-muted me-1" style="font-size:11px;"></i>
                        <span class="tree-node-title" id="tree-title-${node.id}">${node.data?.label || node.data?.tag || node.type}</span>
                    </div>
                    <button type="button" class="el-tree-del-btn" title="Delete element" onclick="event.stopPropagation(); deleteNode('${node.id}')"><i class="fas fa-trash-alt"></i></button>
                `;

                item.ondblclick = (e) => {
                    e.stopPropagation();
                    const titleSpan = item.querySelector('#tree-title-' + node.id);
                    if (!titleSpan) return;

                    const currentLabel = node.data?.label || node.data?.tag || node.type;
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'el-tree-rename-input';
                    input.value = currentLabel;

                    input.onkeydown = (ev) => {
                        if (ev.key === 'Enter') {
                            ev.preventDefault();
                            finishRename();
                        } else if (ev.key === 'Escape') {
                            renderStructureTree();
                        }
                    };

                    input.onblur = finishRename;

                    function finishRename() {
                        const val = input.value.trim() || currentLabel;
                        if (!node.data) node.data = {};
                        node.data.label = val;
                        renderStructureTree();
                    }

                    titleSpan.replaceWith(input);
                    input.focus();
                    input.select();
                };

                container.appendChild(item);

                if (hasKids && !node._treeCollapsed) {
                    renderTreeNodes(node.children, container, depth + 1);
                }
            });
        }

        function toggleTreeCollapse(e, id) {
            e.stopPropagation();
            const node = findNodeById(liveCanvasBlocks, id);
            if (node) {
                node._treeCollapsed = !node._treeCollapsed;
                renderStructureTree();
            }
        }

        function saveElementorVisualLayout() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const payload = {
                title: "{{ $item->title ?? 'Page' }}",
                slug: "{{ $item->slug ?? 'page' }}",
                status: 'published',
                seo_title: "{{ $item->title ?? 'Page' }}",
                seo_description: '',
                seo_keywords: '',
                custom_css: '',
                custom_js: '',
                content_blocks: liveCanvasBlocks
            };

            fetch(apiSaveEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                const toast = document.getElementById('sitToastAlert');
                toast.innerText = data.message || 'Layout published successfully!';
                toast.style.display = 'block';
                setTimeout(() => { toast.style.display = 'none'; }, 2500);

                if (data.slug) {
                    const slugEl = document.getElementById('topBarSlugDisplay');
                    if (slugEl) slugEl.innerText = data.slug;
                    document.getElementById('topPreviewBtn').href = "{{ url('/') }}/" + data.slug;
                }
            })
            .catch(err => {
                showBuilderNotice('Save error: ' + err);
            });
        }
    </script>
</body>
</html>