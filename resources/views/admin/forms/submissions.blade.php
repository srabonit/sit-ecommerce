@extends('layouts.admin')

@section('title', 'Form Submissions & Leads Ledger - ' . config('app.name'))

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Enterprise Typography */
    #formSubmissionsRoot,
    .sub-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Icon Protection */
    #formSubmissionsRoot i,
    #formSubmissionsRoot .fa,
    #formSubmissionsRoot .fas,
    #formSubmissionsRoot .far,
    #formSubmissionsRoot .fab,
    #formSubmissionsRoot .fa-solid,
    #formSubmissionsRoot .fa-regular,
    .sub-modal-scope i,
    .sub-modal-scope .fa,
    .sub-modal-scope .fa-solid,
    .sub-modal-scope .fa-regular {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #formSubmissionsRoot .far,
    #formSubmissionsRoot .fa-regular,
    .sub-modal-scope .far,
    .sub-modal-scope .fa-regular {
        font-weight: 400 !important;
    }

    /* Strict 5px Element Radius Across All Controls */
    #formSubmissionsRoot .btn,
    #formSubmissionsRoot .btn-sm,
    #formSubmissionsRoot button,
    #formSubmissionsRoot .form-control,
    #formSubmissionsRoot .form-select,
    #formSubmissionsRoot .badge,
    #formSubmissionsRoot .alert,
    #formSubmissionsRoot .input-group-text,
    #formSubmissionsRoot .card,
    #formSubmissionsRoot .metric-card,
    #formSubmissionsRoot .fb-header-card,
    #formSubmissionsRoot .fb-table-card,
    #formSubmissionsRoot .filter-card,
    #formSubmissionsRoot #bulkActionBar,
    #formSubmissionsRoot .ip-highlight-box,
    #formSubmissionsRoot .page-link,
    #formSubmissionsRoot .page-item span,
    .sub-modal-scope .btn,
    .sub-modal-scope .form-control,
    .sub-modal-scope .form-select,
    .sub-modal-scope .badge,
    .sub-modal-scope .alert {
        border-radius: 5px !important;
    }

    #formSubmissionsRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #formSubmissionsRoot .input-group > .form-control,
    #formSubmissionsRoot .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Clean Tabular Numbers */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* Clean White Enterprise Header Card */
    .fb-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 17px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
        color: #64748b;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        padding: 12px 14px;
        margin-bottom: 14px;
        border-radius: 6px !important;
    }

    .filter-label {
        font-size: 10.5px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 4px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    #formSubmissionsRoot .form-control,
    #formSubmissionsRoot .form-select,
    .sub-modal-scope .form-control,
    .sub-modal-scope .form-select {
        min-height: 34px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #formSubmissionsRoot .form-control:focus,
    #formSubmissionsRoot .form-select:focus,
    .sub-modal-scope .form-control:focus,
    .sub-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    .fb-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
        border-radius: 6px !important;
    }

    /* Table Architecture: 100% Fit Screen with Zero Horizontal Scroll on Desktop */
    .custom-subs-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .custom-subs-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 10px;
        white-space: nowrap;
    }
    .custom-subs-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-subs-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-subs-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-subs-table th,
        .custom-subs-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 8 Columns Proportional Distribution Summing to Exactly 100% */
        .col-sub-chk     { width: 4% !important; }
        .col-sub-id      { width: 7% !important; }
        .col-sub-form    { width: 17% !important; }
        .col-sub-lead    { width: 24% !important; }
        .col-sub-client  { width: 16% !important; }
        .col-sub-status  { width: 11% !important; }
        .col-sub-date    { width: 11% !important; }
        .col-sub-action  { width: 10% !important; }
    }

    /* Soft Color Badges */
    .badge-soft-success   { background: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .badge-soft-danger    { background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
    .badge-soft-primary   { background: #eff6ff !important; color: #2563eb !important; border: 1px solid #bfdbfe !important; }
    .badge-soft-secondary { background: #f8fafc !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }
    .badge-soft-warning   { background: #fef9c3 !important; color: #854d0e !important; border: 1px solid #fef08a !important; }
    .badge-soft-replied   { background: #f5f3ff !important; color: #6d28d9 !important; border: 1px solid #ddd6fe !important; }

    #bulkActionBar {
        background: #eff6ff;
        border: 1px dashed #93c5fd;
        padding: 8px 12px;
        margin-bottom: 12px;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-radius: 6px !important;
    }

    .modal-detail-table th {
        background-color: #f8fafc;
        width: 32%;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        padding: 7px 10px;
        border: 1px solid #e2e8f0;
    }
    .modal-detail-table td {
        font-size: 11.5px;
        color: #0f172a;
        padding: 7px 10px;
        border: 1px solid #e2e8f0;
        word-break: break-word;
    }

    /* Clean IP Highlight Box */
    .ip-highlight-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 3px 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #334155;
        border-radius: 5px !important;
    }

    /* Minimal Modal System (Screenshot Reference) */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }
    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }
    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 22px !important;
        line-height: 1.45 !important;
    }
    .btn-minimal-delete {
        background-color: #c93424 !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(201, 52, 36, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-delete:hover {
        background-color: #b02717 !important;
        box-shadow: 0 6px 14px rgba(201, 52, 36, 0.38) !important;
        color: #ffffff !important;
    }
    .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        padding: 7px 14px !important;
        box-shadow: none !important;
        transition: color 0.15s ease !important;
        cursor: pointer !important;
    }
    .btn-minimal-cancel:hover {
        color: #0f172a !important;
        background: transparent !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .fb-header-card {
            padding: 12px 14px !important;
        }
        .header-action-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-action-group .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-subs-table {
            min-width: 800px !important;
            table-layout: auto !important;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 80px !important;
        }
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formSubmissionsRoot">

    <!-- 1. Header Banner Card -->
    <div class="fb-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Form Directory
                </a>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle bn-digit font-tabular px-2 py-0.5" id="totalRecordsBadge" style="font-size: 10.5px;">
                    0 Records Found
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fa-solid fa-address-book text-primary"></i>
                <span>Forms & Leads Ledger</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
              Review Leads, Block IPs & Send Replies
            </small>
        </div>

        <div class="header-action-group d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary btn-compact bg-white text-dark shadow-xs" onclick="resetAllFilters()" title="Reset Filters">
                <i class="fa-solid fa-rotate-left me-1 text-secondary"></i> Reset
            </button>
            <button type="button" class="btn btn-success btn-compact shadow-xs fw-bold text-white" onclick="exportFilteredLeads()" title="Export Filtered Leads to CSV" style="background-color: #16a34a !important; border-color: #16a34a !important;">
                <i class="fa-solid fa-file-csv me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- 2. Form Telemetry Status KPI Overview -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Total Submissions</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-tabular stat-val-num" id="kpiTotalCount">
                        --
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">New / Unread</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-tabular stat-val-num" id="kpiNewCount">
                        --
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Replied &amp; Closed</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fa-solid fa-reply-all"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success bn-digit font-tabular stat-val-num" id="kpiHandledCount">
                        --
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Filtered In View</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-tabular stat-val-num" id="kpiFilteredCount">
                        0
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Interactive AJAX Filter Surface -->
    <div class="filter-card">
        <form id="submissionFilterForm" onsubmit="event.preventDefault(); triggerFilterSubmissions();">
            <div class="row g-2">
                <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                    <label class="filter-label">Form Identity</label>
                    <select name="form_id" id="filter_form_id" class="form-select" onchange="triggerFilterSubmissions()">
                        <option value="">All Dynamic Forms</option>
                        @foreach($forms as $f)
                            <option value="{{ $f->id }}" {{ (isset($selectedFormId) && $selectedFormId == $f->id) ? 'selected' : (request('form_id') == $f->id ? 'selected' : '') }}>
                                {{ $f->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-3 col-xl-3">
                    <label class="filter-label">Search Lead / Text / IP</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="filter_search" class="form-control" placeholder="Name, Email, Phone, IP..." value="{{ request('search') }}" onkeyup="debounceFilterSearch()">
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-2 col-xl-2">
                    <label class="filter-label">Status</label>
                    <select name="status" id="filter_status" class="form-select" onchange="triggerFilterSubmissions()">
                        <option value="">All Statuses</option>
                        <option value="new" @selected(request('status') === 'new')>New (Unread)</option>
                        <option value="replied" @selected(request('status') === 'replied')>Replied</option>
                        <option value="read" @selected(request('status') === 'read')>Reviewed / Read</option>
                        <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                        <option value="spam" @selected(request('status') === 'spam')>Flagged Spam</option>
                    </select>
                </div>

                <div class="col-6 col-sm-4 col-md-2 col-xl-2">
                    <label class="filter-label">Date Preset</label>
                    <select name="date_preset" id="filter_date_preset" class="form-select" onchange="applyDatePreset(this.value)">
                        <option value="all" selected>All Time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                    </select>
                </div>

                <div class="col-6 col-sm-4 col-md-2 col-xl-1.5 custom-date-col">
                    <label class="filter-label">From Date</label>
                    <input type="date" name="start_date" id="filter_start_date" class="form-control bn-digit font-tabular" onchange="triggerFilterSubmissions()">
                </div>

                <div class="col-6 col-sm-4 col-md-2 col-xl-1.5 custom-date-col">
                    <label class="filter-label">To Date</label>
                    <input type="date" name="end_date" id="filter_end_date" class="form-control bn-digit font-tabular" onchange="triggerFilterSubmissions()">
                </div>
            </div>
        </form>
    </div>

    <!-- 4. Bulk Actions Toolbar Strip -->
    <div id="bulkActionBar" class="shadow-xs">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check text-primary"></i>
            <span class="small fw-bold text-dark bn-digit font-tabular" id="selectedCountText">0 selected</span>
        </div>
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="openBulkReplyModal()">
                <i class="fa-solid fa-reply-all me-1"></i> Bulk Instant Reply
            </button>
            <button type="button" class="btn btn-outline-secondary btn-compact bg-white" onclick="bulkUpdateStatus('read')">
                <i class="fa-solid fa-envelope-open me-1"></i> Mark Read
            </button>
            <button type="button" class="btn btn-outline-success btn-compact bg-white" onclick="bulkUpdateStatus('contacted')">
                <i class="fa-solid fa-phone me-1"></i> Mark Contacted
            </button>
            <button type="button" class="btn btn-outline-warning btn-compact text-dark bg-white" onclick="bulkUpdateStatus('spam')">
                <i class="fa-solid fa-shield-halved me-1"></i> Mark Spam
            </button>
            <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" onclick="bulkDeleteSubmissions()">
                <i class="fa-solid fa-trash-can me-1"></i> Delete Selected
            </button>
        </div>
    </div>

    <!-- 5. Main Submissions Table -->
    <div class="fb-table-card overflow-hidden">
        <div class="table-responsive pc-fit-container">
            <table class="table align-middle custom-subs-table mb-0" id="submissionsTable">
                <thead>
                    <tr>
                        <th class="text-center ps-3 col-sub-chk">
                            <input class="form-check-input" type="checkbox" id="checkAllSubmissions" onchange="toggleSelectAllSubmissions(this)">
                        </th>
                        <th class="col-sub-id">ID</th>
                        <th class="col-sub-form">Form</th>
                        <th class="col-sub-lead">Customer Lead</th>
                        <th class="col-sub-client">IP &amp; Device</th>
                        <th class="text-center col-sub-status">Status</th>
                        <th class="col-sub-date">Submitted At</th>
                        <th class="text-end pe-3 col-sub-action">Action</th>
                    </tr>
                </thead>
                <tbody id="submissionsTableBody">
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted small">
                            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block text-primary"></i>
                            <span>Loading submissions data...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <small class="text-muted bn-digit font-tabular" id="paginationSummaryText" style="font-size: 11px;">
                Showing 0 records
            </small>
            <div id="submissionsPaginationZone"></div>
        </div>
    </div>

</div>

<!-- MODAL 1: VIEW DETAILS & INSTANT REPLY DUAL DRAWER -->
<div class="modal fade sub-modal-scope" id="leadViewReplyModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fa-solid fa-envelope-open-text text-primary"></i>
                    <span>Customer Lead Details &amp; Instant Email Reply</span>
                    <span class="badge bg-light text-dark bn-digit font-tabular border" id="mEntryIdBadge">#0</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3 bg-light">
                <div class="row g-3">
                    <!-- Left Column: Customer Submitted Data -->
                    <div class="col-12 col-lg-6">
                        <div class="bg-white border p-3 mb-2 shadow-xs" style="border-radius: 6px;">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <strong class="d-block text-dark" style="font-size: 13px;" id="mFormNameText">Form</strong>
                                    <small class="text-muted" id="mSubmittedAtText" style="font-size: 11px;"></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-soft-warning text-uppercase" id="mCurrentStatusBadge" style="font-size: 9.5px; font-weight: 700;">New</span>
                                    <div class="small text-muted mt-1" id="mRepliedAuditTag" style="font-size: 10px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border p-3 shadow-xs" style="border-radius: 6px;">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 12.5px;">
                                <i class="fa-solid fa-list-check text-success me-1"></i> Customer Submitted Payload
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm modal-detail-table mb-0">
                                    <tbody id="mFieldsTableBody">
                                        <!-- Dynamic payload rows populated here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- IP Block & Security Area -->
                            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="ip-highlight-box">
                                    <i class="fa-solid fa-network-wired text-primary"></i>
                                    <span id="mClientIpText">127.0.0.1</span>
                                </span>
                                <button type="button" class="btn btn-outline-danger btn-compact" id="btnModalBlockIp" onclick="executeBlockIp()">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Block IP &amp; Blacklist
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Email Instant Reply Composer -->
                    <div class="col-12 col-lg-6">
                        <div class="bg-white border p-3 shadow-xs h-100 d-flex flex-column" style="border-radius: 6px;">
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3" style="font-size: 12.5px;">
                                <i class="fa-solid fa-paper-plane me-1"></i> Compose Instant Reply
                            </h6>

                            <form id="instantReplyForm" onsubmit="event.preventDefault(); sendInstantReply();">
                                <div class="mb-2">
                                    <label class="filter-label">Recipient Email</label>
                                    <input type="email" id="reply_to_email" class="form-control" readonly style="background: #f8fafc; font-weight: 600;">
                                </div>

                                <div class="mb-2">
                                    <label class="filter-label">Use Existing Email Template (Optional)</label>
                                    <select id="reply_template_picker" class="form-select" onchange="applyEmailTemplateToReply(this.value)">
                                        <option value="">-- Custom Email Message --</option>
                                        @foreach($emailTemplates ?? [] as $tpl)
                                            <option value="{{ $tpl->id }}" data-subject="{{ $tpl->subject }}" data-body="{{ $tpl->html_content }}">
                                                {{ $tpl->name }} ({{ $tpl->subject ?: 'No subject' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="filter-label">Email Subject <span class="text-danger">*</span></label>
                                    <input type="text" id="reply_subject" class="form-control" required placeholder="Regarding your inquiry on {{ config('app.name') }}...">
                                </div>

                                <div class="mb-3 flex-grow-1">
                                    <label class="filter-label">Message Body <span class="text-danger">*</span></label>
                                    <textarea id="reply_message" class="form-control" rows="8" required placeholder="Type your response to the customer here... (Supports {name}, {store_name}, {form_name})" style="font-size: 12px;"></textarea>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                        Tags: <code>{name}</code>, <code>{store_name}</code>, <code>{form_name}</code>
                                    </small>
                                </div>

                                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                    <button type="submit" class="btn btn-primary btn-compact px-4 shadow-xs fw-bold" id="btnSendReplySubmit">
                                        <span class="btn-text"><i class="fa-solid fa-paper-plane me-1"></i> Send Reply Email</span>
                                        <span class="btn-spinner d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-compact" id="btnModalDeleteLead">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Submission
                </button>
                <button type="button" class="btn btn-light btn-compact px-4 border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: BULK INSTANT REPLY MODAL -->
<div class="modal fade sub-modal-scope" id="bulkReplyModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fa-solid fa-reply-all me-1 text-primary"></i> Bulk Instant Reply to Selected Leads
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="alert alert-info py-2 small mb-3" id="bulkRecipientAlertText" style="font-size: 11.5px; border-radius: 5px;">
                    Sending email to 0 selected leads.
                </div>

                <div class="mb-2">
                    <label class="filter-label">Template (Optional)</label>
                    <select id="bulk_reply_template_picker" class="form-select" onchange="applyBulkTemplate(this.value)">
                        <option value="">-- Custom Message --</option>
                        @foreach($emailTemplates ?? [] as $tpl)
                            <option value="{{ $tpl->id }}" data-subject="{{ $tpl->subject }}" data-body="{{ $tpl->html_content }}">
                                {{ $tpl->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="filter-label">Subject</label>
                    <input type="text" id="bulk_reply_subject" class="form-control" placeholder="Update from {{ config('app.name') }}...">
                </div>

                <div class="mb-2">
                    <label class="filter-label">Message</label>
                    <textarea id="bulk_reply_message" class="form-control" rows="5" placeholder="Hello {name}, thank you for reaching out to us..."></textarea>
                    <small class="text-muted d-block mt-1" style="font-size: 10px;">
                        Tags: <code>{name}</code>, <code>{email}</code>, <code>{store_name}</code>
                    </small>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-compact px-4 shadow-xs fw-bold" id="btnExecuteBulkReply" onclick="executeBulkReply()">
                    Send Bulk Emails
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: MINIMAL CONFIRMATION MODAL (Exact Match with Reference Screenshot) -->
<div class="modal fade sub-modal-scope" id="submissionConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 109000;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="confirmModalTitle">Are you sure you wanna delete this?</h6>
            <p class="minimal-modal-subtext" id="confirmModalMessage">
                This submission record will be permanently removed.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-delete" id="btnExecuteConfirmAction">Delete</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: MINIMAL NOTICE MODAL -->
<div class="modal fade sub-modal-scope" id="submissionNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 109100;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="noticeModalTitle">Notification</h6>
            <p class="minimal-modal-subtext" id="noticeModalMessage" style="margin-bottom: 16px !important;"></p>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-delete" style="background-color: #2563eb !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let debounceTimer = null;
let currentActiveSubmission = null;
let pendingActionCallback = null;
let rawSubmissionsStore = {};
const csrfToken = '{{ csrf_token() }}';
const assetBaseUrl = '{{ asset('') }}';

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function formatDateTime(rawDate) {
    if (!rawDate) return '<span class="text-muted">N/A</span>';
    const d = new Date(rawDate);
    if (isNaN(d.getTime())) {
        return `<span class="text-dark">${escapeHtml(rawDate)}</span>`;
    }

    const day = String(d.getDate()).padStart(2, '0');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = months[d.getMonth()];
    const year = d.getFullYear();

    let hours = d.getHours();
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const strHours = String(hours).padStart(2, '0');

    return `
        <div class="d-flex flex-column" style="line-height: 1.35;">
            <strong class="text-dark bn-digit font-tabular" style="font-size: 11px;">${day} ${month} ${year}</strong>
            <small class="text-muted bn-digit font-tabular" style="font-size: 10px;">
                <i class="fa-regular fa-clock me-1 text-primary"></i>${strHours}:${minutes} ${ampm}
            </small>
        </div>
    `;
}

function showNotice(msg, title = 'Notification') {
    document.getElementById('noticeModalTitle').innerText = title;
    document.getElementById('noticeModalMessage').innerText = msg;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('submissionNoticeModal')).show();
}

function showConfirm(title, msg, callback, btnText = 'Delete', btnClass = 'btn-minimal-delete') {
    document.getElementById('confirmModalTitle').innerText = title;
    document.getElementById('confirmModalMessage').innerHTML = msg;
    const btn = document.getElementById('btnExecuteConfirmAction');
    btn.className = btnClass;
    btn.innerText = btnText;
    pendingActionCallback = callback;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('submissionConfirmModal')).show();
}

document.getElementById('btnExecuteConfirmAction')?.addEventListener('click', function() {
    bootstrap.Modal.getInstance(document.getElementById('submissionConfirmModal'))?.hide();
    if (typeof pendingActionCallback === 'function') {
        pendingActionCallback();
        pendingActionCallback = null;
    }
});

function debounceFilterSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentPage = 1;
        triggerFilterSubmissions();
    }, 350);
}

function applyDatePreset(preset) {
    const today = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

    let start = '';
    let end   = '';

    if (preset === 'today') {
        start = end = fmt(today);
    } else if (preset === 'yesterday') {
        const y = new Date(today);
        y.setDate(y.getDate() - 1);
        start = end = fmt(y);
    } else if (preset === 'this_week') {
        const day = today.getDay();
        const diff = today.getDate() - day + (day === 0 ? -6 : 1);
        const mon = new Date(today.setDate(diff));
        start = fmt(mon);
        end = fmt(new Date());
    } else if (preset === 'this_month') {
        start = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-01`;
        end = fmt(new Date());
    } else if (preset === 'last_month') {
        const firstDayPrevMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const lastDayPrevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
        start = fmt(firstDayPrevMonth);
        end = fmt(lastDayPrevMonth);
    } else if (preset === 'this_year') {
        start = `${today.getFullYear()}-01-01`;
        end = fmt(new Date());
    }

    document.getElementById('filter_start_date').value = start;
    document.getElementById('filter_end_date').value = end;
    currentPage = 1;
    triggerFilterSubmissions();
}

function resetAllFilters() {
    document.getElementById('submissionFilterForm').reset();
    document.getElementById('filter_start_date').value = '';
    document.getElementById('filter_end_date').value = '';
    currentPage = 1;
    triggerFilterSubmissions();
}

function triggerFilterSubmissions(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('submissionsTableBody');
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted small"><i class="fa-solid fa-spinner fa-spin me-1 text-primary"></i> Fetching submissions...</td></tr>`;

    const formId     = document.getElementById('filter_form_id').value;
    const search     = document.getElementById('filter_search').value;
    const status     = document.getElementById('filter_status').value;
    const startDate  = document.getElementById('filter_start_date').value;
    const endDate    = document.getElementById('filter_end_date').value;

    const params = new URLSearchParams({
        page: currentPage,
        form_id: formId,
        search: search,
        status: status,
        start_date: startDate,
        end_date: endDate,
        ajax: 1
    });

    fetch(`{{ route('admin.forms.submissions.index') }}?` + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        renderSubmissionsTable(data);
    })
    .catch(() => {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger small"><i class="fa-solid fa-circle-exclamation me-1"></i> Failed to load submissions data. Please refresh.</td></tr>`;
    });
}

function renderSubmissionsTable(res) {
    const tbody = document.getElementById('submissionsTableBody');
    const badge = document.getElementById('totalRecordsBadge');
    const summary = document.getElementById('paginationSummaryText');
    const paginationZone = document.getElementById('submissionsPaginationZone');

    rawSubmissionsStore = {};
    const submissions = res.data || [];
    const total = res.total || 0;

    if (badge) badge.innerText = `${total} Record(s)`;
    if (summary) summary.innerText = `Showing ${res.from || 0} to ${res.to || 0} of ${total} record(s)`;

    const kpiFiltered = document.getElementById('kpiFilteredCount');
    if (kpiFiltered) kpiFiltered.innerText = total;

    const kpiTotal = document.getElementById('kpiTotalCount');
    if (kpiTotal && res.kpi_total !== undefined) kpiTotal.innerText = res.kpi_total;
    else if (kpiTotal) kpiTotal.innerText = total;

    const kpiNew = document.getElementById('kpiNewCount');
    if (kpiNew && res.kpi_new !== undefined) kpiNew.innerText = res.kpi_new;

    const kpiHandled = document.getElementById('kpiHandledCount');
    if (kpiHandled && res.kpi_handled !== undefined) kpiHandled.innerText = res.kpi_handled;

    if (!submissions.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted small"><i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-40"></i>No submissions match your specified filter criteria.</td></tr>`;
        if (paginationZone) paginationZone.innerHTML = '';
        return;
    }

    let rowsHtml = '';
    submissions.forEach(sub => {
        rawSubmissionsStore[sub.id] = sub;

        const leadEmail = sub.customer_email || '';
        const leadName  = sub.customer_name || 'Customer';

        let statusBadge = '';
        switch(sub.status) {
            case 'replied':
                statusBadge = `<span class="badge badge-soft-replied px-2 py-0.5 text-uppercase" style="font-size: 9.5px; font-weight: 700;" title="Replied by: ${escapeHtml(sub.replied_by_name || 'Team')}">
                    <i class="fa-solid fa-reply me-1"></i>Replied
                </span>`;
                break;
            case 'read':
            case 'reviewed':
                statusBadge = '<span class="badge badge-soft-primary px-2 py-0.5 text-uppercase" style="font-size: 9.5px; font-weight: 700;">Reviewed</span>';
                break;
            case 'contacted':
                statusBadge = '<span class="badge badge-soft-success px-2 py-0.5 text-uppercase" style="font-size: 9.5px; font-weight: 700;">Contacted</span>';
                break;
            case 'closed':
                statusBadge = '<span class="badge badge-soft-secondary px-2 py-0.5 text-uppercase" style="font-size: 9.5px; font-weight: 700;">Closed</span>';
                break;
            case 'spam':
                statusBadge = '<span class="badge badge-soft-danger px-2 py-0.5 text-uppercase" style="font-size: 9.5px; font-weight: 700;">Spam</span>';
                break;
            default:
                statusBadge = '<span class="badge badge-soft-warning px-2 py-0.5 text-uppercase" style="font-size: 9.5px; font-weight: 700;">New</span>';
                break;
        }

        const formattedDateCol = formatDateTime(sub.submitted_at || sub.created_at);

        rowsHtml += `
            <tr id="sub-row-${sub.id}">
                <td class="text-center ps-3 col-sub-chk">
                    <input class="form-check-input sub-check-item" type="checkbox" value="${sub.id}" onchange="updateSelectedCount()">
                </td>
                <td class="text-muted small col-sub-id bn-digit font-tabular">#${sub.id}</td>
                <td class="col-sub-form">
                    <strong class="text-dark d-block text-truncate" style="max-width: 160px; font-size: 12px;" title="${escapeHtml(sub.form?.name || '')}">
                        ${escapeHtml(sub.form?.name || 'General Form')}
                    </strong>
                    <small class="text-muted" style="font-size: 10.5px;">/${escapeHtml(sub.form?.slug || '')}</small>
                </td>
                <td class="col-sub-lead">
                    <div class="min-w-0">
                        <strong class="text-dark d-block" style="font-size: 12px;">${escapeHtml(leadName)}</strong>
                        ${leadEmail ? `<small class="text-primary d-block text-truncate fw-semibold" style="font-size: 11px;"><i class="fa-regular fa-envelope me-1"></i>${escapeHtml(leadEmail)}</small>` : '<small class="text-muted" style="font-size:10px;">No Email Detected</small>'}
                    </div>
                </td>
                <td class="col-sub-client">
                    <div class="d-flex flex-column gap-1">
                        <span class="ip-highlight-box" style="width: fit-content;" title="Client Real IP Address">
                            <i class="fa-solid fa-network-wired text-primary" style="font-size: 9px;"></i>
                            <span>${escapeHtml(sub.ip_address || '127.0.0.1')}</span>
                        </span>
                        <small class="text-muted text-truncate d-block" style="max-width: 160px; font-size: 10px;" title="${escapeHtml(sub.user_agent || '')}">
                            ${sub.user_agent ? escapeHtml(sub.user_agent.substring(0, 30)) + '...' : 'Browser'}
                        </small>
                    </div>
                </td>
                <td class="text-center col-sub-status">
                    ${statusBadge}
                    ${sub.replied_by_name ? `<small class="text-muted d-block" style="font-size: 9.5px;">by ${escapeHtml(sub.replied_by_name)}</small>` : ''}
                </td>
                <td class="col-sub-date">
                    ${formattedDateCol}
                </td>
                <td class="text-end pe-3 col-sub-action">
                    <div class="d-inline-flex align-items-center gap-1">
                        <button type="button" class="btn btn-primary btn-compact p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 26px; height: 26px; font-size: 10.5px;" onclick="openLeadModal(${sub.id})" title="View Message &amp; Instant Reply">
                            <i class="fa-solid fa-reply"></i>
                        </button>

                        <div class="dropdown">
                            <button class="btn btn-light border p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 26px; height: 26px; font-size: 10px;" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="font-size: 11.5px; border-radius: 5px;">
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="quickUpdateStatus(${sub.id}, 'new')"><i class="fa-solid fa-circle-dot text-warning me-1"></i> Mark New</a></li>
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="quickUpdateStatus(${sub.id}, 'contacted')"><i class="fa-solid fa-phone text-success me-1"></i> Mark Contacted</a></li>
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="quickUpdateStatus(${sub.id}, 'closed')"><i class="fa-solid fa-circle-check text-secondary me-1"></i> Mark Closed</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item py-1 text-warning" href="javascript:void(0)" onclick="singleBlockIp(${sub.id}, '${sub.ip_address}')"><i class="fa-solid fa-shield-halved me-1"></i> Block IP Address</a></li>
                                <li><a class="dropdown-item py-1 text-danger" href="javascript:void(0)" onclick="deleteSingleSubmission(${sub.id})"><i class="fa-solid fa-trash-can me-1"></i> Delete Submission</a></li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = rowsHtml;
    renderPagination(res);
    updateSelectedCount();
}

function renderPagination(res) {
    const zone = document.getElementById('submissionsPaginationZone');
    if (!zone || !res.last_page || res.last_page <= 1) {
        if (zone) zone.innerHTML = '';
        return;
    }

    let p = '<ul class="pagination pagination-sm m-0 gap-1">';
    if (res.current_page > 1) {
        p += `<li class="page-item"><button class="page-link" onclick="triggerFilterSubmissions(${res.current_page - 1})">&laquo;</button></li>`;
    }
    for (let i = 1; i <= res.last_page; i++) {
        if (i === 1 || i === res.last_page || (i >= res.current_page - 1 && i <= res.current_page + 1)) {
            p += `<li class="page-item ${i === res.current_page ? 'active' : ''}"><button class="page-link" onclick="triggerFilterSubmissions(${i})">${i}</button></li>`;
        } else if (i === res.current_page - 2 || i === res.current_page + 2) {
            p += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    if (res.current_page < res.last_page) {
        p += `<li class="page-item"><button class="page-link" onclick="triggerFilterSubmissions(${res.current_page + 1})">&raquo;</button></li>`;
    }
    p += '</ul>';
    zone.innerHTML = p;
}

// 5. Open Lead Modal & Instant Reply Drawer
function openLeadModal(id) {
    const sub = rawSubmissionsStore[id];
    if (!sub) return;

    currentActiveSubmission = sub;
    document.getElementById('mEntryIdBadge').innerText = '#' + sub.id;
    document.getElementById('mFormNameText').innerText = sub.form?.name || 'Form Submission';
    document.getElementById('mSubmittedAtText').innerText = 'Received: ' + (sub.submitted_at || sub.created_at);
    document.getElementById('mCurrentStatusBadge').innerText = (sub.status || 'new').toUpperCase();
    document.getElementById('mClientIpText').innerText = sub.ip_address || '127.0.0.1';

    const auditTag = document.getElementById('mRepliedAuditTag');
    if (sub.status === 'replied' && sub.replied_by_name) {
        auditTag.innerHTML = `<i class="fa-solid fa-check-double text-success me-1"></i>Replied by ${escapeHtml(sub.replied_by_name)}`;
    } else {
        auditTag.innerHTML = '';
    }

    // Populate Fields Table
    const tbody = document.getElementById('mFieldsTableBody');
    let fRows = '';
    if (Array.isArray(sub.values) && sub.values.length) {
        sub.values.forEach(v => {
            const label = v.field?.label || v.field?.name || 'Field';
            let val = v.value;

            if (val === null || val === undefined || val === '') {
                val = '<span class="text-muted font-italic">—</span>';
            } else if (typeof val === 'string' && (val.includes('uploads/forms/') || /\.(jpg|jpeg|png|webp|pdf|docx?|zip|xlsx?|txt)$/i.test(val))) {
                const fileUrl = assetBaseUrl + val.replace(/^\/+/, '');
                val = `<a href="${fileUrl}" target="_blank" class="btn btn-outline-primary btn-compact shadow-xs"><i class="fa-solid fa-download me-1"></i> View Attached File</a>`;
            } else {
                val = escapeHtml(val).replace(/\n/g, '<br>');
            }

            fRows += `<tr><th>${escapeHtml(label)}</th><td>${val}</td></tr>`;
        });
    } else {
        fRows = `<tr><td colspan="2" class="text-center text-muted py-3">No values recorded.</td></tr>`;
    }
    tbody.innerHTML = fRows;

    // Prep Reply Box
    const emailInput = document.getElementById('reply_to_email');
    emailInput.value = sub.customer_email || '';
    document.getElementById('reply_subject').value = `Regarding your inquiry on ${sub.form?.name || 'our store'}`;
    document.getElementById('reply_message').value = '';
    document.getElementById('reply_template_picker').value = '';

    document.getElementById('btnModalDeleteLead').onclick = () => {
        bootstrap.Modal.getInstance(document.getElementById('leadViewReplyModal'))?.hide();
        deleteSingleSubmission(sub.id);
    };

    bootstrap.Modal.getOrCreateInstance(document.getElementById('leadViewReplyModal')).show();
}

function applyEmailTemplateToReply(templateId) {
    if (!templateId) return;
    const select = document.getElementById('reply_template_picker');
    const opt = select.options[select.selectedIndex];
    if (opt) {
        const sub = opt.getAttribute('data-subject');
        const body = opt.getAttribute('data-body');
        if (sub) document.getElementById('reply_subject').value = sub;
        if (body) {
            document.getElementById('reply_message').value = body.replace(/<\/?[^>]+(>|$)/g, "\n").trim();
        }
    }
}

function sendInstantReply() {
    if (!currentActiveSubmission) return;

    const email = document.getElementById('reply_to_email').value;
    const subject = document.getElementById('reply_subject').value;
    const message = document.getElementById('reply_message').value;

    if (!email) {
        showNotice('No valid customer email address found for this lead.', 'Notice');
        return;
    }

    const btn = document.getElementById('btnSendReplySubmit');
    const btnText = btn.querySelector('.btn-text');
    const btnSpinner = btn.querySelector('.btn-spinner');

    btn.disabled = true;
    if (btnText) btnText.classList.add('d-none');
    if (btnSpinner) btnSpinner.classList.remove('d-none');

    fetch(`{{ url('admin/forms/submissions') }}/${currentActiveSubmission.id}/reply`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            subject: subject,
            message: message
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (btnText) btnText.classList.remove('d-none');
        if (btnSpinner) btnSpinner.classList.add('d-none');

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('leadViewReplyModal'))?.hide();
            showNotice(data.message, 'Email Dispatched');
            triggerFilterSubmissions(currentPage);
        } else {
            showNotice(data.message || 'Failed to dispatch reply email.', 'Error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        if (btnText) btnText.classList.remove('d-none');
        if (btnSpinner) btnSpinner.classList.add('d-none');
        showNotice('Network error occurred.', 'Error');
    });
}

// 6. Block IP Address (Minimal Modal)
function executeBlockIp() {
    if (!currentActiveSubmission) return;
    const id = currentActiveSubmission.id;
    const ip = currentActiveSubmission.ip_address;

    showConfirm('Are you sure you wanna block this IP?', `All future form requests from IP <strong>[${ip}]</strong> will be blocked.`, () => {
        fetch(`{{ url('admin/forms/submissions') }}/${id}/block-ip`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('leadViewReplyModal'))?.hide();
            showNotice(data.message, 'IP Blocked');
            triggerFilterSubmissions(currentPage);
        })
        .catch(() => triggerFilterSubmissions(currentPage));
    }, 'Block IP');
}

function singleBlockIp(id, ip) {
    showConfirm('Are you sure you wanna block this IP?', `All future requests from IP <strong>[${ip}]</strong> will be blocked.`, () => {
        fetch(`{{ url('admin/forms/submissions') }}/${id}/block-ip`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            showNotice(data.message, 'Blocked');
            triggerFilterSubmissions(currentPage);
        });
    }, 'Block IP');
}

// 7. Bulk Reply
function openBulkReplyModal() {
    const ids = getSelectedIds();
    if (!ids.length) return;

    document.getElementById('bulkRecipientAlertText').innerText = `Sending instant reply to ${ids.length} selected lead(s).`;
    document.getElementById('bulk_reply_subject').value = `Update regarding your inquiry`;
    document.getElementById('bulk_reply_message').value = `Hello {name},\n\nThank you for reaching out to us. We have reviewed your request and would like to follow up.`;
    document.getElementById('bulk_reply_template_picker').value = '';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkReplyModal')).show();
}

function applyBulkTemplate(templateId) {
    if (!templateId) return;
    const select = document.getElementById('bulk_reply_template_picker');
    const opt = select.options[select.selectedIndex];
    if (opt) {
        const sub = opt.getAttribute('data-subject');
        const body = opt.getAttribute('data-body');
        if (sub) document.getElementById('bulk_reply_subject').value = sub;
        if (body) {
            document.getElementById('bulk_reply_message').value = body.replace(/<\/?[^>]+(>|$)/g, "\n").trim();
        }
    }
}

function executeBulkReply() {
    const ids = getSelectedIds();
    const subject = document.getElementById('bulk_reply_subject').value;
    const message = document.getElementById('bulk_reply_message').value;

    if (!ids.length || !subject || !message) {
        showNotice('Please provide both subject and message body.', 'Notice');
        return;
    }

    const btn = document.getElementById('btnExecuteBulkReply');
    btn.disabled = true;
    btn.innerText = 'Dispatching emails...';

    fetch(`{{ route('admin.forms.submissions.bulk_reply') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ids: ids,
            subject: subject,
            message: message
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = 'Send Bulk Emails';
        bootstrap.Modal.getInstance(document.getElementById('bulkReplyModal'))?.hide();

        if (data.success) {
            showNotice(data.message, 'Bulk Dispatched');
            triggerFilterSubmissions(currentPage);
        } else {
            showNotice(data.message || 'Bulk dispatch failed.', 'Error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerText = 'Send Bulk Emails';
        showNotice('Network error occurred.', 'Error');
    });
}

// Single Delete & Status Handlers (Minimal Modal Trigger)
function quickUpdateStatus(id, newStatus) {
    fetch(`{{ url('admin/forms/submissions') }}/${id}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            triggerFilterSubmissions(currentPage);
        }
    });
}

function deleteSingleSubmission(id) {
    showConfirm('Are you sure you wanna delete this?', `Submission record <strong>#${id}</strong> will be permanently removed.`, () => {
        fetch(`{{ url('admin/forms/submissions') }}/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _method: 'DELETE' })
        })
        .then(r => r.json())
        .then(data => {
            showNotice('Submission removed.', 'Deleted');
            triggerFilterSubmissions(currentPage);
        });
    });
}

function toggleSelectAllSubmissions(master) {
    document.querySelectorAll('.sub-check-item').forEach(chk => chk.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.sub-check-item:checked');
    const bar = document.getElementById('bulkActionBar');
    const countText = document.getElementById('selectedCountText');

    if (checked.length > 0) {
        bar.style.display = 'flex';
        countText.innerText = `${checked.length} submission(s) selected`;
    } else {
        bar.style.display = 'none';
        const master = document.getElementById('checkAllSubmissions');
        if (master) master.checked = false;
    }
}

function getSelectedIds() {
    const ids = [];
    document.querySelectorAll('.sub-check-item:checked').forEach(c => ids.push(c.value));
    return ids;
}

function bulkUpdateStatus(newStatus) {
    const ids = getSelectedIds();
    if (!ids.length) return;

    fetch(`{{ route('admin.forms.submissions.bulk_status') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids, status: newStatus })
    })
    .then(r => r.json())
    .then(() => {
        showNotice(`Selected submissions updated to ${newStatus}.`, 'Updated');
        triggerFilterSubmissions(currentPage);
    });
}

function bulkDeleteSubmissions() {
    const ids = getSelectedIds();
    if (!ids.length) return;

    showConfirm('Are you sure you wanna delete this?', `Delete <strong>${ids.length}</strong> selected submissions permanently?`, () => {
        fetch(`{{ route('admin.forms.submissions.bulk_destroy') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, _method: 'DELETE' })
        })
        .then(r => r.json())
        .then(() => {
            showNotice('Submissions removed.', 'Deleted');
            triggerFilterSubmissions(currentPage);
        });
    });
}

function exportFilteredLeads() {
    const formId     = document.getElementById('filter_form_id').value;
    const search     = document.getElementById('filter_search').value;
    const status     = document.getElementById('filter_status').value;
    const startDate  = document.getElementById('filter_start_date').value;
    const endDate    = document.getElementById('filter_end_date').value;

    const params = new URLSearchParams({
        export: 'csv',
        form_id: formId,
        search: search,
        status: status,
        start_date: startDate,
        end_date: endDate
    });

    window.location.href = `{{ route('admin.forms.submissions.index') }}?` + params.toString();
}

document.addEventListener('DOMContentLoaded', function () {
    triggerFilterSubmissions(1);
});
</script>
@endsection