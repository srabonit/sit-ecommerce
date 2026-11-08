
@extends('layouts.admin')

@section('title', 'Form Submissions - ' . config('app.name'))

@section('admin_content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       FORM SUBMISSIONS - PAGE STYLES
    ========================================================= */

    #formSubmissionsRoot,
    .sub-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        color: #1e293b;
        letter-spacing: -0.01em;
    }

    #formSubmissionsRoot *,
    .sub-modal-scope * {
        box-sizing: border-box;
    }

    /* Font Awesome */
    #formSubmissionsRoot i,
    .sub-modal-scope i {
        font-family: "Font Awesome 6 Free" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
    }

    #formSubmissionsRoot .far,
    #formSubmissionsRoot .fa-regular,
    .sub-modal-scope .far,
    .sub-modal-scope .fa-regular {
        font-weight: 400 !important;
    }

    /* Global Radius */
    #formSubmissionsRoot .btn,
    #formSubmissionsRoot button,
    #formSubmissionsRoot .form-control,
    #formSubmissionsRoot .form-select,
    #formSubmissionsRoot .badge,
    #formSubmissionsRoot .alert,
    #formSubmissionsRoot .input-group-text,
    #formSubmissionsRoot .card,
    #formSubmissionsRoot .page-link,
    #formSubmissionsRoot .ip-highlight-box,
    .sub-modal-scope .btn,
    .sub-modal-scope .form-control,
    .sub-modal-scope .form-select,
    .sub-modal-scope .badge,
    .sub-modal-scope .alert {
        border-radius: 5px !important;
    }

    #formSubmissionsRoot .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #formSubmissionsRoot .input-group > .form-control,
    #formSubmissionsRoot .input-group > .form-select {
        border-radius: 0 5px 5px 0 !important;
    }

    .bn-digit,
    .font-tabular {
        font-variant-numeric: tabular-nums;
    }

    /* Buttons */
    #formSubmissionsRoot .btn-compact,
    .sub-modal-scope .btn-compact {
        min-height: 32px;
        padding: 6px 11px !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #formSubmissionsRoot .btn-primary,
    .sub-modal-scope .btn-primary {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
    }

    #formSubmissionsRoot .btn-primary:hover,
    .sub-modal-scope .btn-primary:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }

    #formSubmissionsRoot .btn-outline-secondary,
    .sub-modal-scope .btn-outline-secondary {
        background: #fff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }

    #formSubmissionsRoot .btn-outline-secondary:hover,
    .sub-modal-scope .btn-outline-secondary:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
    }

    #formSubmissionsRoot .btn:focus,
    .sub-modal-scope .btn:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    /* Header */
    #formSubmissionsRoot .fb-header-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 15px 17px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
    }

    #formSubmissionsRoot .page-heading {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.4;
    }

    #formSubmissionsRoot .page-description {
        font-size: 11px;
        color: #64748b;
        margin-top: 3px;
    }

    #formSubmissionsRoot .header-action-group {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    /* KPI Cards */
    #formSubmissionsRoot .metric-card {
        height: 100%;
        min-height: 82px;
        padding: 12px 13px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #formSubmissionsRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #formSubmissionsRoot .metric-card-blue {
        background: linear-gradient(180deg, #fff, #f0f9ff);
        border-left: 3px solid #2563eb !important;
    }

    #formSubmissionsRoot .metric-card-amber {
        background: linear-gradient(180deg, #fff, #fffbeb);
        border-left: 3px solid #f59e0b !important;
    }

    #formSubmissionsRoot .metric-card-green {
        background: linear-gradient(180deg, #fff, #f0fdf4);
        border-left: 3px solid #10b981 !important;
    }

    #formSubmissionsRoot .metric-card-purple {
        background: linear-gradient(180deg, #fff, #faf5ff);
        border-left: 3px solid #a855f7 !important;
    }

    #formSubmissionsRoot .stat-label-title {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        line-height: 1.3;
    }

    #formSubmissionsRoot .stat-val-num {
        font-size: 19px;
        font-weight: 800;
        line-height: 1.25;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #formSubmissionsRoot .metric-icon-square {
        width: 31px;
        height: 31px;
        min-width: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-size: 12px;
    }

    /* Filter Card */
    #formSubmissionsRoot .filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 13px;
        margin-bottom: 13px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #formSubmissionsRoot .filter-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    #formSubmissionsRoot .form-control,
    #formSubmissionsRoot .form-select,
    .sub-modal-scope .form-control,
    .sub-modal-scope .form-select {
        min-height: 35px;
        padding: 6px 10px;
        font-size: 11.5px;
        font-weight: 500;
        color: #0f172a;
        background-color: #fff;
        border: 1px solid #cbd5e1;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #formSubmissionsRoot .form-control:focus,
    #formSubmissionsRoot .form-select:focus,
    .sub-modal-scope .form-control:focus,
    .sub-modal-scope .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #formSubmissionsRoot .form-control::placeholder,
    .sub-modal-scope .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    /* Table */
    #formSubmissionsRoot .fb-table-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
        overflow: hidden;
    }

    #formSubmissionsRoot .pc-fit-container {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    #formSubmissionsRoot .custom-subs-table {
        width: 100%;
        min-width: 1050px;
        margin-bottom: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    #formSubmissionsRoot .custom-subs-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .035em;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 9px;
        white-space: nowrap;
    }

    #formSubmissionsRoot .custom-subs-table td {
        padding: 9px;
        vertical-align: middle;
        font-size: 11px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    #formSubmissionsRoot .custom-subs-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #formSubmissionsRoot .custom-subs-table tbody tr:hover td {
        background: #f8fafc;
    }

    #formSubmissionsRoot .col-sub-chk { width: 4%; }
    #formSubmissionsRoot .col-sub-id { width: 7%; }
    #formSubmissionsRoot .col-sub-form { width: 16%; }
    #formSubmissionsRoot .col-sub-lead { width: 22%; }
    #formSubmissionsRoot .col-sub-client { width: 17%; }
    #formSubmissionsRoot .col-sub-status { width: 11%; }
    #formSubmissionsRoot .col-sub-date { width: 13%; }
    #formSubmissionsRoot .col-sub-action { width: 10%; }

    /* Status Badges */
    #formSubmissionsRoot .badge-soft-success {
        background: #dcfce7 !important;
        color: #15803d !important;
        border: 1px solid #bbf7d0 !important;
    }

    #formSubmissionsRoot .badge-soft-danger {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border: 1px solid #fecaca !important;
    }

    #formSubmissionsRoot .badge-soft-primary {
        background: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #bfdbfe !important;
    }

    #formSubmissionsRoot .badge-soft-secondary {
        background: #f8fafc !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
    }

    #formSubmissionsRoot .badge-soft-warning {
        background: #fef9c3 !important;
        color: #854d0e !important;
        border: 1px solid #fef08a !important;
    }

    #formSubmissionsRoot .badge-soft-replied {
        background: #f5f3ff !important;
        color: #6d28d9 !important;
        border: 1px solid #ddd6fe !important;
    }

    /* Bulk Actions */
    #formSubmissionsRoot #bulkActionBar {
        display: none;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 9px 12px;
        margin-bottom: 12px;
        background: #eff6ff;
        border: 1px dashed #93c5fd;
        border-radius: 5px;
    }

    /* IP Box */
    #formSubmissionsRoot .ip-highlight-box,
    .sub-modal-scope .ip-highlight-box {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 10px;
        font-weight: 600;
        border-radius: 5px;
    }

    /* Pagination */
    #formSubmissionsRoot .pagination {
        flex-wrap: wrap;
    }

    #formSubmissionsRoot .page-link {
        min-width: 29px;
        min-height: 29px;
        padding: 5px 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10.5px;
        color: #475569;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    #formSubmissionsRoot .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    /* Detail Table */
    .sub-modal-scope .modal-detail-table {
        width: 100%;
    }

    .sub-modal-scope .modal-detail-table th {
        width: 32%;
        background: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
    }

    .sub-modal-scope .modal-detail-table td {
        color: #0f172a;
        font-size: 11px;
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
        word-break: break-word;
    }

    /* Modals */
    .sub-modal-scope .modal-content {
        border: 1px solid #e2e8f0 !important;
        border-radius: 7px !important;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(15, 23, 42, .12) !important;
    }

    .sub-modal-scope .modal-header {
        padding: 11px 15px;
    }

    .sub-modal-scope .modal-body {
        padding: 15px;
    }

    .sub-modal-scope .modal-footer {
        padding: 9px 15px;
    }

    .sub-modal-scope .minimal-modal-card {
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 7px !important;
        padding: 22px !important;
        text-align: left !important;
    }

    .sub-modal-scope .minimal-modal-title {
        font-size: 15px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin-bottom: 7px !important;
        letter-spacing: -.02em !important;
    }

    .sub-modal-scope .minimal-modal-subtext {
        font-size: 12px !important;
        color: #64748b !important;
        margin-bottom: 20px !important;
        line-height: 1.5 !important;
    }

    .sub-modal-scope .btn-minimal-delete {
        background: #dc3545 !important;
        border: none !important;
        color: #fff !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        padding: 8px 18px !important;
        border-radius: 5px !important;
        transition: all .15s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .sub-modal-scope .btn-minimal-delete:hover {
        background: #bb2d3b !important;
        color: #fff !important;
    }

    .sub-modal-scope .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        padding: 8px 12px !important;
        cursor: pointer;
    }

    .sub-modal-scope .btn-minimal-cancel:hover {
        color: #0f172a !important;
    }

    /* Checkbox */
    #formSubmissionsRoot .form-check-input {
        width: 15px;
        height: 15px;
        margin-top: 0;
        border-color: #cbd5e1;
        cursor: pointer;
    }

    #formSubmissionsRoot .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (min-width: 1200px) {
        #formSubmissionsRoot .custom-subs-table {
            min-width: 1050px;
        }
    }

    @media (max-width: 991.98px) {
        #formSubmissionsRoot .custom-subs-table {
            min-width: 1000px;
        }

        #formSubmissionsRoot .fb-header-card {
            align-items: flex-start !important;
        }

        #formSubmissionsRoot #bulkActionBar {
            align-items: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        #formSubmissionsRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #formSubmissionsRoot .fb-header-card {
            padding: 12px;
        }

        #formSubmissionsRoot .page-heading {
            font-size: 15px;
        }

        #formSubmissionsRoot .page-description {
            font-size: 10px;
        }

        #formSubmissionsRoot .header-action-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        #formSubmissionsRoot .header-action-group .btn {
            width: 100%;
            min-height: 34px;
            font-size: 10.5px !important;
        }

        #formSubmissionsRoot .metric-card {
            min-height: 76px;
            padding: 10px;
        }

        #formSubmissionsRoot .stat-label-title {
            font-size: 9px;
        }

        #formSubmissionsRoot .stat-val-num {
            font-size: 17px;
        }

        #formSubmissionsRoot .metric-icon-square {
            width: 27px;
            height: 27px;
            min-width: 27px;
            font-size: 10px;
        }

        #formSubmissionsRoot .filter-card {
            padding: 10px;
        }

        #formSubmissionsRoot #bulkActionBar {
            flex-direction: column;
            align-items: stretch;
        }

        #formSubmissionsRoot #bulkActionBar > div:last-child {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        #formSubmissionsRoot #bulkActionBar .btn {
            width: 100%;
            white-space: normal;
            min-height: 34px;
            font-size: 9.5px !important;
        }

        #formSubmissionsRoot .fb-table-card {
            border-radius: 5px;
        }

        .sub-modal-scope .modal-dialog {
            margin: 8px;
        }

        .sub-modal-scope .modal-body {
            padding: 10px;
        }

        .sub-modal-scope .modal-header {
            padding: 10px;
        }

        .sub-modal-scope .modal-footer {
            padding: 8px 10px;
        }

        .sub-modal-scope .minimal-modal-card {
            padding: 18px !important;
        }
    }

    @media (max-width: 400px) {
        #formSubmissionsRoot .metric-card {
            padding: 8px;
        }

        #formSubmissionsRoot .stat-label-title {
            font-size: 8.5px;
        }

        #formSubmissionsRoot .stat-val-num {
            font-size: 16px;
        }

        #formSubmissionsRoot .filter-label {
            font-size: 9px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-3 py-3" id="formSubmissionsRoot">

    <!-- HEADER -->
    <div class="fb-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                    <i class="fa-solid fa-arrow-left text-primary"></i> Forms
                </a>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle bn-digit px-2 py-2" id="totalRecordsBadge" style="font-size: 10px;">
                    0 Records
                </span>
            </div>

            <h5 class="page-heading mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-address-book text-primary"></i>
                Form Submissions
            </h5>

            <small class="page-description d-block">
                Manage leads and replies
            </small>
        </div>

        <div class="header-action-group">
            <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs" onclick="resetAllFilters()" title="Reset Filters">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>

            <button type="button" class="btn btn-success btn-compact shadow-xs fw-bold text-white" onclick="exportFilteredLeads()" title="Export CSV" style="background-color: #16a34a !important; border-color: #16a34a !important;">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-2 mb-3">

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                    <span class="stat-label-title text-truncate">Total Leads</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                </div>
                <h4 class="stat-val-num mb-0" id="kpiTotalCount">--</h4>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                    <span class="stat-label-title text-truncate">New / Unread</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                </div>
                <h4 class="stat-val-num text-warning-emphasis mb-0" id="kpiNewCount">--</h4>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                    <span class="stat-label-title text-truncate">Handled</span>
                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fa-solid fa-reply-all"></i>
                    </div>
                </div>
                <h4 class="stat-val-num text-success mb-0" id="kpiHandledCount">--</h4>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                    <span class="stat-label-title text-truncate">Filtered</span>
                    <div class="metric-icon-square" style="background: #f3e8ff; color: #7e22ce;">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                </div>
                <h4 class="stat-val-num mb-0" id="kpiFilteredCount">0</h4>
            </div>
        </div>

    </div>
<!-- FILTERS -->
<div class="filter-card">
    <form id="submissionFilterForm" onsubmit="event.preventDefault(); triggerFilterSubmissions();">
        <div class="d-flex flex-wrap flex-xl-nowrap align-items-center gap-2">

            <div class="flex-fill" style="min-width: 140px;">
                <label class="filter-label mb-1" style="font-size: 11px;">Form</label>
                <select name="form_id" id="filter_form_id" class="form-select form-select-sm" onchange="triggerFilterSubmissions()">
                    <option value="">All Forms</option>
                    @foreach($forms as $f)
                        <option value="{{ $f->id }}" {{ (isset($selectedFormId) && $selectedFormId == $f->id) ? 'selected' : (request('form_id') == $f->id ? 'selected' : '') }}>
                            {{ $f->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-fill" style="min-width: 180px;">
                <label class="filter-label mb-1" style="font-size: 11px;">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted">
                        <i class="fa-solid fa-magnifying-glass" style="font-size: 11px;"></i>
                    </span>
                    <input type="text" name="search" id="filter_search" class="form-control form-control-sm" placeholder="Name, email, phone, IP..." value="{{ request('search') }}" onkeyup="debounceFilterSearch()">
                </div>
            </div>

            <div class="flex-fill" style="min-width: 130px;">
                <label class="filter-label mb-1" style="font-size: 11px;">Status</label>
                <select name="status" id="filter_status" class="form-select form-select-sm" onchange="triggerFilterSubmissions()">
                    <option value="">All Statuses</option>
                    <option value="new" @selected(request('status') === 'new')>New</option>
                    <option value="replied" @selected(request('status') === 'replied')>Replied</option>
                    <option value="read" @selected(request('status') === 'read')>Read</option>
                    <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                    <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                    <option value="spam" @selected(request('status') === 'spam')>Spam</option>
                </select>
            </div>

            <div class="flex-fill" style="min-width: 130px;">
                <label class="filter-label mb-1" style="font-size: 11px;">Date Range</label>
                <select name="date_preset" id="filter_date_preset" class="form-select form-select-sm" onchange="applyDatePreset(this.value)">
                    <option value="all" selected>All Time</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                </select>
            </div>

            <div class="flex-fill" style="min-width: 125px;">
                <label class="filter-label mb-1" style="font-size: 11px;">From</label>
                <input type="date" name="start_date" id="filter_start_date" class="form-control form-control-sm bn-digit font-tabular" onchange="triggerFilterSubmissions()">
            </div>

            <div class="flex-fill" style="min-width: 125px;">
                <label class="filter-label mb-1" style="font-size: 11px;">To</label>
                <input type="date" name="end_date" id="filter_end_date" class="form-control form-control-sm bn-digit font-tabular" onchange="triggerFilterSubmissions()">
            </div>

        </div>
    </form>
</div>
    <!-- BULK ACTIONS -->
    <div id="bulkActionBar" class="shadow-xs">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check text-primary"></i>
            <span class="small fw-bold text-dark bn-digit" id="selectedCountText">0 selected</span>
        </div>

        <div class="d-flex align-items-center gap-1 flex-wrap">
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="openBulkReplyModal()">
                <i class="fa-solid fa-reply-all"></i> Bulk Reply
            </button>

            <button type="button" class="btn btn-outline-secondary btn-compact bg-white" onclick="bulkUpdateStatus('read')">
                <i class="fa-solid fa-envelope-open"></i> Mark Read
            </button>

            <button type="button" class="btn btn-outline-success btn-compact bg-white" onclick="bulkUpdateStatus('contacted')">
                <i class="fa-solid fa-phone"></i> Contacted
            </button>

            <button type="button" class="btn btn-outline-warning btn-compact text-dark bg-white" onclick="bulkUpdateStatus('spam')">
                <i class="fa-solid fa-shield-halved"></i> Spam
            </button>

            <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" onclick="bulkDeleteSubmissions()">
                <i class="fa-solid fa-trash-can"></i> Delete
            </button>
        </div>
    </div>

    <!-- SUBMISSIONS TABLE -->
    <div class="fb-table-card">
        <div class="table-responsive pc-fit-container">
            <table class="table align-middle custom-subs-table mb-0" id="submissionsTable">
                <thead>
                    <tr>
                        <th class="text-center ps-3 col-sub-chk">
                            <input class="form-check-input" type="checkbox" id="checkAllSubmissions" onchange="toggleSelectAllSubmissions(this)">
                        </th>
                        <th class="col-sub-id">ID</th>
                        <th class="col-sub-form">Form</th>
                        <th class="col-sub-lead">Customer</th>
                        <th class="col-sub-client">IP &amp; Browser</th>
                        <th class="text-center col-sub-status">Status</th>
                        <th class="col-sub-date">Date</th>
                        <th class="text-end pe-3 col-sub-action">Actions</th>
                    </tr>
                </thead>

                <tbody id="submissionsTableBody">
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted small">
                            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block text-primary"></i>
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <small class="text-muted bn-digit font-tabular" id="paginationSummaryText" style="font-size: 10.5px;">
                Showing 0 records
            </small>
            <div id="submissionsPaginationZone"></div>
        </div>
    </div>

</div>

<!-- MODAL 1: LEAD DETAILS & REPLY -->
<div class="modal fade sub-modal-scope" id="leadViewReplyModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header border-bottom bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 12.5px;">
                    <i class="fa-solid fa-envelope-open-text text-primary"></i>
                    Lead Details
                    <span class="badge bg-light text-dark bn-digit border" id="mEntryIdBadge">#0</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body bg-light">
                <div class="row g-3">

                    <!-- CUSTOMER DATA -->
                    <div class="col-12 col-lg-6">

                        <div class="bg-white border p-3 mb-2 shadow-xs" style="border-radius: 5px;">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <strong class="d-block text-dark" style="font-size: 12px;" id="mFormNameText">Form</strong>
                                    <small class="text-muted" id="mSubmittedAtText" style="font-size: 10px;"></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-soft-warning text-uppercase" id="mCurrentStatusBadge" style="font-size: 9px; font-weight: 700;">New</span>
                                    <div class="small text-muted mt-1" id="mRepliedAuditTag" style="font-size: 9px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border p-3 shadow-xs" style="border-radius: 5px;">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 11.5px;">
                                <i class="fa-solid fa-list-check text-success me-1"></i> Submitted Data
                            </h6>

                            <div class="table-responsive">
                                <table class="table table-sm modal-detail-table mb-0">
                                    <tbody id="mFieldsTableBody">
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="ip-highlight-box">
                                    <i class="fa-solid fa-network-wired text-primary"></i>
                                    <span id="mClientIpText">127.0.0.1</span>
                                </span>

                                <button type="button" class="btn btn-outline-danger btn-compact" id="btnModalBlockIp" onclick="executeBlockIp()">
                                    <i class="fa-solid fa-shield-halved"></i> Block IP
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- REPLY FORM -->
                    <div class="col-12 col-lg-6">
                        <div class="bg-white border p-3 shadow-xs h-100 d-flex flex-column" style="border-radius: 5px;">
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3" style="font-size: 11.5px;">
                                <i class="fa-solid fa-paper-plane me-1"></i> Send Reply
                            </h6>

                            <form id="instantReplyForm" onsubmit="event.preventDefault(); sendInstantReply();">

                                <div class="mb-2">
                                    <label class="filter-label">Recipient</label>
                                    <input type="email" id="reply_to_email" class="form-control" readonly style="background: #f8fafc; font-weight: 600;">
                                </div>

                                <div class="mb-2">
                                    <label class="filter-label">Email Template</label>
                                    <select id="reply_template_picker" class="form-select" onchange="applyEmailTemplateToReply(this.value)">
                                        <option value="">Custom Message</option>
                                        @foreach($emailTemplates ?? [] as $tpl)
                                            <option value="{{ $tpl->id }}" data-subject="{{ $tpl->subject }}" data-body="{{ $tpl->html_content }}">
                                                {{ $tpl->name }} ({{ $tpl->subject ?: 'No subject' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="filter-label">Subject <span class="text-danger">*</span></label>
                                    <input type="text" id="reply_subject" class="form-control" required placeholder="Regarding your inquiry on {{ config('app.name') }}...">
                                </div>

                                <div class="mb-3 flex-grow-1">
                                    <label class="filter-label">Message <span class="text-danger">*</span></label>
                                    <textarea id="reply_message" class="form-control" rows="8" required placeholder="Write your reply... (Supports {name}, {store_name}, {form_name})" style="font-size: 11.5px;"></textarea>

                                    <small class="text-muted d-block mt-1" style="font-size: 9.5px;">
                                        Tags: <code>{name}</code>, <code>{store_name}</code>, <code>{form_name}</code>
                                    </small>
                                </div>

                                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                    <button type="submit" class="btn btn-primary btn-compact px-4 shadow-xs fw-bold" id="btnSendReplySubmit">
                                        <span class="btn-text"><i class="fa-solid fa-paper-plane me-1"></i> Send Reply</span>
                                        <span class="btn-spinner d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...</span>
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-compact" id="btnModalDeleteLead">
                    <i class="fa-solid fa-trash-can"></i> Delete Lead
                </button>

                <button type="button" class="btn btn-light btn-compact px-4 border" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL 2: BULK REPLY -->
<div class="modal fade sub-modal-scope" id="bulkReplyModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-bottom bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark" style="font-size: 12px;">
                    <i class="fa-solid fa-reply-all me-1 text-primary"></i> Bulk Reply
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3" id="bulkRecipientAlertText" style="font-size: 10.5px;">
                    Sending to 0 selected leads.
                </div>

                <div class="mb-2">
                    <label class="filter-label">Template</label>
                    <select id="bulk_reply_template_picker" class="form-select" onchange="applyBulkTemplate(this.value)">
                        <option value="">Custom Message</option>
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
                    <textarea id="bulk_reply_message" class="form-control" rows="5" placeholder="Hello {name}, thank you for contacting us..."></textarea>

                    <small class="text-muted d-block mt-1" style="font-size: 9.5px;">
                        Tags: <code>{name}</code>, <code>{email}</code>, <code>{store_name}</code>
                    </small>
                </div>
            </div>

            <div class="modal-footer border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-primary btn-compact px-4 shadow-xs fw-bold" id="btnExecuteBulkReply" onclick="executeBulkReply()">
                    Send Replies
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL 3: CONFIRMATION -->
<div class="modal fade sub-modal-scope" id="submissionConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 109000;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title" id="confirmModalTitle">Confirm Action</h6>

            <p class="minimal-modal-subtext" id="confirmModalMessage">
                This record will be permanently removed.
            </p>

            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-delete" id="btnExecuteConfirmAction">Delete</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL 4: NOTICE -->
<div class="modal fade sub-modal-scope" id="submissionNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 109100;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title" id="noticeModalTitle">Notification</h6>

            <p class="minimal-modal-subtext" id="noticeModalMessage" style="margin-bottom: 16px !important;"></p>

            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-delete" style="background-color: #2563eb !important;" data-bs-dismiss="modal">
                    OK
                </button>
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
            <strong class="text-dark bn-digit font-tabular" style="font-size: 10.5px;">${day} ${month} ${year}</strong>
            <small class="text-muted bn-digit font-tabular" style="font-size: 9.5px;">
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
    let end = '';

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

    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4 text-muted small">
                <i class="fa-solid fa-spinner fa-spin me-1 text-primary"></i> Loading...
            </td>
        </tr>
    `;

    const formId = document.getElementById('filter_form_id').value;
    const search = document.getElementById('filter_search').value;
    const status = document.getElementById('filter_status').value;
    const startDate = document.getElementById('filter_start_date').value;
    const endDate = document.getElementById('filter_end_date').value;

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
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-danger small">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    Failed to load data. Please refresh.
                </td>
            </tr>
        `;
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

    if (badge) badge.innerText = `${total} Records`;

    if (summary) {
        summary.innerText = `Showing ${res.from || 0} to ${res.to || 0} of ${total}`;
    }

    const kpiFiltered = document.getElementById('kpiFilteredCount');
    if (kpiFiltered) kpiFiltered.innerText = total;

    const kpiTotal = document.getElementById('kpiTotalCount');

    if (kpiTotal && res.kpi_total !== undefined) {
        kpiTotal.innerText = res.kpi_total;
    } else if (kpiTotal) {
        kpiTotal.innerText = total;
    }

    const kpiNew = document.getElementById('kpiNewCount');
    if (kpiNew && res.kpi_new !== undefined) {
        kpiNew.innerText = res.kpi_new;
    }

    const kpiHandled = document.getElementById('kpiHandledCount');
    if (kpiHandled && res.kpi_handled !== undefined) {
        kpiHandled.innerText = res.kpi_handled;
    }

    if (!submissions.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted small">
                    <i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-40"></i>
                    No matching submissions found.
                </td>
            </tr>
        `;

        if (paginationZone) paginationZone.innerHTML = '';

        return;
    }

    let rowsHtml = '';

    submissions.forEach(sub => {
        rawSubmissionsStore[sub.id] = sub;

        const leadEmail = sub.customer_email || '';
        const leadName = sub.customer_name || 'Customer';

        let statusBadge = '';

        switch(sub.status) {
            case 'replied':
                statusBadge = `<span class="badge badge-soft-replied px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700;" title="Replied by: ${escapeHtml(sub.replied_by_name || 'Team')}">
                    <i class="fa-solid fa-reply me-1"></i>Replied
                </span>`;
                break;

            case 'read':
            case 'reviewed':
                statusBadge = '<span class="badge badge-soft-primary px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700;">Read</span>';
                break;

            case 'contacted':
                statusBadge = '<span class="badge badge-soft-success px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700;">Contacted</span>';
                break;

            case 'closed':
                statusBadge = '<span class="badge badge-soft-secondary px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700;">Closed</span>';
                break;

            case 'spam':
                statusBadge = '<span class="badge badge-soft-danger px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700;">Spam</span>';
                break;

            default:
                statusBadge = '<span class="badge badge-soft-warning px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700;">New</span>';
                break;
        }

        const formattedDateCol = formatDateTime(sub.submitted_at || sub.created_at);

        rowsHtml += `
            <tr id="sub-row-${sub.id}">
                <td class="text-center ps-3 col-sub-chk">
                    <input class="form-check-input sub-check-item" type="checkbox" value="${sub.id}" onchange="updateSelectedCount()">
                </td>

                <td class="text-muted small col-sub-id bn-digit font-tabular">
                    #${sub.id}
                </td>

                <td class="col-sub-form">
                    <strong class="text-dark d-block text-truncate" style="max-width: 160px; font-size: 11px;" title="${escapeHtml(sub.form?.name || '')}">
                        ${escapeHtml(sub.form?.name || 'General Form')}
                    </strong>
                    <small class="text-muted" style="font-size: 9.5px;">/${escapeHtml(sub.form?.slug || '')}</small>
                </td>

                <td class="col-sub-lead">
                    <div class="min-w-0">
                        <strong class="text-dark d-block" style="font-size: 11px;">
                            ${escapeHtml(leadName)}
                        </strong>

                        ${leadEmail
                            ? `<small class="text-primary d-block text-truncate fw-semibold" style="font-size: 10px;"><i class="fa-regular fa-envelope me-1"></i>${escapeHtml(leadEmail)}</small>`
                            : '<small class="text-muted" style="font-size: 9px;">No email</small>'
                        }
                    </div>
                </td>

                <td class="col-sub-client">
                    <div class="d-flex flex-column gap-1">
                        <span class="ip-highlight-box" style="width: fit-content;" title="Client IP">
                            <i class="fa-solid fa-network-wired text-primary" style="font-size: 9px;"></i>
                            <span>${escapeHtml(sub.ip_address || '127.0.0.1')}</span>
                        </span>

                        <small class="text-muted text-truncate d-block" style="max-width: 160px; font-size: 9px;" title="${escapeHtml(sub.user_agent || '')}">
                            ${sub.user_agent ? escapeHtml(sub.user_agent.substring(0, 30)) + '...' : 'Browser'}
                        </small>
                    </div>
                </td>

                <td class="text-center col-sub-status">
                    ${statusBadge}
                    ${sub.replied_by_name ? `<small class="text-muted d-block mt-1" style="font-size: 9px;">by ${escapeHtml(sub.replied_by_name)}</small>` : ''}
                </td>

                <td class="col-sub-date">
                    ${formattedDateCol}
                </td>

                <td class="text-end pe-3 col-sub-action">
                    <div class="d-inline-flex align-items-center gap-1">

                        <button type="button" class="btn btn-primary btn-compact p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 27px; height: 27px; font-size: 10px;" onclick="openLeadModal(${sub.id})" title="View & Reply">
                            <i class="fa-solid fa-reply"></i>
                        </button>

                        <div class="dropdown">
                            <button class="btn btn-light border p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 27px; height: 27px; font-size: 10px;" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="font-size: 10.5px; border-radius: 5px;">
                                <li>
                                    <a class="dropdown-item py-1" href="javascript:void(0)" onclick="quickUpdateStatus(${sub.id}, 'new')">
                                        <i class="fa-solid fa-circle-dot text-warning me-1"></i> Mark New
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item py-1" href="javascript:void(0)" onclick="quickUpdateStatus(${sub.id}, 'contacted')">
                                        <i class="fa-solid fa-phone text-success me-1"></i> Contacted
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item py-1" href="javascript:void(0)" onclick="quickUpdateStatus(${sub.id}, 'closed')">
                                        <i class="fa-solid fa-circle-check text-secondary me-1"></i> Close
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider my-1"></li>

                                <li>
                                    <a class="dropdown-item py-1 text-warning" href="javascript:void(0)" onclick="singleBlockIp(${sub.id}, '${sub.ip_address}')">
                                        <i class="fa-solid fa-shield-halved me-1"></i> Block IP
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item py-1 text-danger" href="javascript:void(0)" onclick="deleteSingleSubmission(${sub.id})">
                                        <i class="fa-solid fa-trash-can me-1"></i> Delete
                                    </a>
                                </li>
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

// OPEN LEAD MODAL
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

                val = `<a href="${fileUrl}" target="_blank" class="btn btn-outline-primary btn-compact shadow-xs"><i class="fa-solid fa-download me-1"></i> View File</a>`;
            } else {
                val = escapeHtml(val).replace(/\n/g, '<br>');
            }

            fRows += `<tr><th>${escapeHtml(label)}</th><td>${val}</td></tr>`;
        });
    } else {
        fRows = `<tr><td colspan="2" class="text-center text-muted py-3">No data found.</td></tr>`;
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
        showNotice('No customer email found.', 'Notice');
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

            showNotice(data.message, 'Email Sent');
            triggerFilterSubmissions(currentPage);
        } else {
            showNotice(data.message || 'Failed to send reply.', 'Error');
        }
    })
    .catch(() => {
        btn.disabled = false;

        if (btnText) btnText.classList.remove('d-none');
        if (btnSpinner) btnSpinner.classList.add('d-none');

        showNotice('Network error occurred.', 'Error');
    });
}

// BLOCK IP
function executeBlockIp() {
    if (!currentActiveSubmission) return;

    const id = currentActiveSubmission.id;
    const ip = currentActiveSubmission.ip_address;

    showConfirm('Block this IP?', `Future form requests from IP <strong>[${ip}]</strong> will be blocked.`, () => {
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
    showConfirm('Block this IP?', `Future requests from IP <strong>[${ip}]</strong> will be blocked.`, () => {
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

// BULK REPLY
function openBulkReplyModal() {
    const ids = getSelectedIds();

    if (!ids.length) return;

    document.getElementById('bulkRecipientAlertText').innerText = `Sending reply to ${ids.length} selected leads.`;

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
        showNotice('Enter subject and message.', 'Notice');
        return;
    }

    const btn = document.getElementById('btnExecuteBulkReply');

    btn.disabled = true;
    btn.innerText = 'Sending...';

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
        btn.innerText = 'Send Replies';

        bootstrap.Modal.getInstance(document.getElementById('bulkReplyModal'))?.hide();

        if (data.success) {
            showNotice(data.message, 'Bulk Sent');
            triggerFilterSubmissions(currentPage);
        } else {
            showNotice(data.message || 'Bulk sending failed.', 'Error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerText = 'Send Replies';

        showNotice('Network error occurred.', 'Error');
    });
}

// STATUS UPDATE
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

// SINGLE DELETE
function deleteSingleSubmission(id) {
    showConfirm('Delete this lead?', `Submission <strong>#${id}</strong> will be permanently removed.`, () => {
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

// SELECTION
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
        countText.innerText = `${checked.length} selected`;
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

// BULK STATUS
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
        showNotice(`Selected leads updated to ${newStatus}.`, 'Updated');
        triggerFilterSubmissions(currentPage);
    });
}

// BULK DELETE
function bulkDeleteSubmissions() {
    const ids = getSelectedIds();

    if (!ids.length) return;

    showConfirm('Delete selected leads?', `Delete <strong>${ids.length}</strong> selected submissions permanently?`, () => {
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

// CSV EXPORT
function exportFilteredLeads() {
    const formId = document.getElementById('filter_form_id').value;
    const search = document.getElementById('filter_search').value;
    const status = document.getElementById('filter_status').value;
    const startDate = document.getElementById('filter_start_date').value;
    const endDate = document.getElementById('filter_end_date').value;

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
