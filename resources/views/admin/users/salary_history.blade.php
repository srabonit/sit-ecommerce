@extends('layouts.admin')

@section('title', 'Salary History - ' . ($staff->name ?? 'Staff') . ' - ' . config('app.name', 'Shop Now BD'))

@php
    $basicSalary = (float) ($staff->salary ?? 0.00);
    $joiningDate = !empty($staff->joining_date) ? date('d M Y', strtotime($staff->joining_date)) : 'Not Defined';
@endphp

@section('admin_content')
<style>
    #salaryHistoryRoot {
        --sh-border: #e2e8f0;
        --sh-muted: #64748b;
        --sh-text: #1e293b;
        --sh-bg: #f8fafc;
        color: var(--sh-text);
    }

    #salaryHistoryRoot .card,
    #salaryHistoryRoot .btn,
    #salaryHistoryRoot .badge,
    #salaryHistoryRoot .metric-card,
    #salaryHistoryRoot .staff-avatar-box {
        border-radius: 5px !important;
    }

    #salaryHistoryRoot .font-monospace {
        font-weight: 600 !important;
    }

    #salaryHistoryRoot .btn-compact {
        font-size: 11px !important;
        padding: 6px 11px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #salaryHistoryRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    #salaryHistoryRoot .page-heading {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -.3px;
    }

    #salaryHistoryRoot .section-card {
        background: #fff;
        border: 1px solid var(--sh-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
    }

    #salaryHistoryRoot .metric-card {
        padding: 13px;
        border: 1px solid var(--sh-border);
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-width: 0;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    #salaryHistoryRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
    }

    #salaryHistoryRoot .stat-label-title {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .02em;
        line-height: 1.3;
    }

    #salaryHistoryRoot .stat-val-num {
        font-size: 17px;
        line-height: 1.3;
        letter-spacing: -.3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #salaryHistoryRoot .metric-card-green {
        background: linear-gradient(180deg, #fff 0%, #f0fdf4 100%);
        border-color: #bbf7d0;
    }

    #salaryHistoryRoot .metric-card-red {
        background: linear-gradient(180deg, #fff 0%, #fef2f2 100%);
        border-color: #fecaca;
    }

    #salaryHistoryRoot .metric-card-blue {
        background: linear-gradient(180deg, #fff 0%, #f0f9ff 100%);
        border-color: #bae6fd;
    }

    #salaryHistoryRoot .metric-card-purple {
        background: linear-gradient(180deg, #fff 0%, #faf5ff 100%);
        border-color: #e9d5ff;
    }

    #salaryHistoryRoot .staff-profile-card {
        border: 1px solid var(--sh-border);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
    }

    #salaryHistoryRoot .staff-avatar-box {
        width: 50px;
        height: 50px;
        min-width: 50px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        font-weight: 700;
    }

    #salaryHistoryRoot .staff-info {
        min-width: 0;
        line-height: 1.4;
    }

    #salaryHistoryRoot .staff-name {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    #salaryHistoryRoot .staff-meta {
        font-size: 10.5px;
        color: var(--sh-muted);
        overflow-wrap: anywhere;
    }

    #salaryHistoryRoot .salary-summary {
        padding-left: 16px;
        border-left: 1px solid var(--sh-border);
    }

    #salaryHistoryRoot .salary-summary-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        color: var(--sh-muted);
    }

    #salaryHistoryRoot .salary-summary-value {
        font-size: 21px;
        line-height: 1.3;
        font-weight: 700;
        color: #2563eb;
        white-space: nowrap;
    }

    #salaryHistoryRoot .ledger-header {
        padding: 11px 14px;
        background: #fff;
        border-bottom: 1px solid var(--sh-border);
    }

    #salaryHistoryRoot .ledger-title {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
    }

    #salaryHistoryRoot .custom-history-table {
        width: 100%;
        min-width: 920px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #salaryHistoryRoot .custom-history-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        border-bottom: 1px solid var(--sh-border);
        padding: 11px 9px;
        white-space: nowrap;
    }

    #salaryHistoryRoot .custom-history-table td {
        padding: 10px 9px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    #salaryHistoryRoot .custom-history-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #salaryHistoryRoot .custom-history-table tbody tr {
        transition: background .15s ease;
    }

    #salaryHistoryRoot .custom-history-table tbody tr:hover {
        background: #f8fafc;
    }

    #salaryHistoryRoot .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #salaryHistoryRoot .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    #salaryHistoryRoot .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    #salaryHistoryRoot .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 5px;
    }

    #salaryHistoryRoot .empty-state {
        padding: 45px 15px !important;
        white-space: normal !important;
    }

    #salaryHistoryRoot .empty-state i {
        color: #94a3b8;
    }

    @media (max-width: 767.98px) {
        #salaryHistoryRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #salaryHistoryRoot .history-header-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        #salaryHistoryRoot .history-header-actions .btn {
            width: 100%;
            white-space: normal;
            min-height: 34px;
        }

        #salaryHistoryRoot .page-heading {
            font-size: 15px;
        }

        #salaryHistoryRoot .staff-profile-card {
            padding: 12px !important;
        }

        #salaryHistoryRoot .salary-summary {
            width: 100%;
            border-left: 0;
            border-top: 1px solid var(--sh-border);
            padding: 10px 0 0;
        }

        #salaryHistoryRoot .metric-card {
            padding: 10px;
        }

        #salaryHistoryRoot .stat-val-num {
            font-size: 15px;
        }

        #salaryHistoryRoot .stat-label-title {
            font-size: 9.5px;
        }

        #salaryHistoryRoot .ledger-header {
            padding: 10px;
        }

        #salaryHistoryRoot .custom-history-table {
            min-width: 920px;
        }
    }

    @media (max-width: 380px) {
        #salaryHistoryRoot .history-header-actions {
            grid-template-columns: 1fr;
        }

        #salaryHistoryRoot .stat-val-num {
            font-size: 14px;
        }

        #salaryHistoryRoot .metric-card {
            padding: 9px;
        }

        #salaryHistoryRoot .staff-avatar-box {
            width: 42px;
            height: 42px;
            min-width: 42px;
            font-size: 17px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="salaryHistoryRoot">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div class="w-100">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="{{ route('admin.payroll.index') }}" class="btn btn-sm btn-light border btn-compact text-secondary shadow-xs">
                    <i class="fas fa-arrow-left"></i>
                    Salary Sheet
                </a>

                <span class="text-muted small">/</span>

                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    ID: #{{ $staff->id }}
                </span>
            </div>

            <h5 class="page-heading mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-history text-primary"></i>
                Salary History
            </h5>
        </div>

        <div class="history-header-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.roles.users.show', $staff->id) }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-user"></i>
                Staff Profile
            </a>

            <a href="{{ route('admin.roles.users.edit_staff', $staff->id) }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs">
                <i class="fas fa-pen-to-square"></i>
                Edit Salary
            </a>
        </div>
    </div>

    {{-- Staff Profile --}}
    <div class="staff-profile-card card border-0 p-3 mb-3">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3 min-w-0 w-100">
                <div class="staff-avatar-box shadow-xs">
                    {{ strtoupper(substr($staff->name ?? 'S', 0, 1)) }}
                </div>

                <div class="staff-info">
                    <h6 class="staff-name mb-1 text-truncate">
                        {{ $staff->name }}
                    </h6>

                    <div class="staff-meta mb-2">
                        <span class="me-2">
                            <i class="fas fa-envelope me-1"></i>{{ $staff->email }}
                        </span>

                        <span>
                            <i class="fas fa-phone me-1"></i>{{ $staff->phone ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 10px;">
                            {{ ucfirst($staff->role ?? 'Staff') }}
                        </span>

                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 10px;">
                            <i class="fas fa-calendar-alt me-1 text-muted"></i>
                            Joined: {{ $joiningDate }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Basic Salary --}}
            <div class="salary-summary text-start text-sm-end">
                <small class="salary-summary-label d-block">
                    BASIC SALARY
                </small>

                <div class="salary-summary-value font-monospace">
                    ৳ {{ number_format($basicSalary, 2) }}
                    <span class="fw-normal text-muted" style="font-size: 11px;">/ month</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Salary Overview --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-blue">
                <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                    <span class="text-muted stat-label-title text-truncate">Payroll Months</span>
                    <i class="fas fa-calendar-days text-primary"></i>
                </div>

                <h5 class="fw-bold my-0 text-dark font-monospace stat-val-num">
                    {{ $history->count() }} Cycles
                </h5>

                <small class="text-muted" style="font-size: 10px;">
                    Monthly records
                </small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-green">
                <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                    <span class="text-muted stat-label-title text-truncate">Total Paid</span>
                    <i class="fas fa-circle-check text-success"></i>
                </div>

                <h5 class="fw-bold my-0 text-success font-monospace stat-val-num">
                    ৳ {{ number_format($totalPaid, 2) }}
                </h5>

                <small class="text-muted" style="font-size: 10px;">
                    Paid salary
                </small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-red">
                <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                    <span class="text-muted stat-label-title text-truncate">Pending Salary</span>
                    <i class="fas fa-clock text-danger"></i>
                </div>

                <h5 class="fw-bold my-0 text-danger font-monospace stat-val-num">
                    ৳ {{ number_format($totalPending, 2) }}
                </h5>

                <small class="text-muted" style="font-size: 10px;">
                    Due amount
                </small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-purple">
                <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                    <span class="text-muted stat-label-title text-truncate">Daily Rate</span>
                    <i class="fas fa-calculator text-dark"></i>
                </div>

                <h5 class="fw-bold my-0 text-dark font-monospace stat-val-num">
                    ৳ {{ number_format(round($basicSalary / 30, 2), 2) }}
                </h5>

                <small class="text-muted" style="font-size: 10px;">
                    Based on 30 days
                </small>
            </div>
        </div>

    </div>

    {{-- Salary Ledger --}}
    <div class="card section-card border-0 p-0 overflow-hidden mb-3">

        <div class="ledger-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="ledger-title">
                <i class="fas fa-receipt text-primary me-1"></i>
                Payment Records
            </span>

            <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">
                {{ $history->count() }} Records
            </span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 custom-history-table">

                <thead>
                    <tr>
                        <th class="ps-3">Month</th>
                        <th>Voucher</th>
                        <th class="text-center">Days</th>
                        <th class="text-end">Basic</th>
                        <th class="text-end">Bonus</th>
                        <th class="text-end">Deduction</th>
                        <th class="text-end">Net Salary</th>
                        <th class="text-center">Status</th>
                        <th>Payment Account</th>
                        <th>Paid Date</th>
                        <th class="text-end pe-3">Receipt</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($history as $row)
                        @php
                            $isPaid = $row->status === 'paid';
                            $monthFormatted = \Carbon\Carbon::createFromDate($row->salary_year, $row->salary_month, 1)->format('F Y');
                        @endphp

                        <tr>
                            {{-- Month --}}
                            <td class="ps-3 fw-bold text-dark font-monospace">
                                {{ $monthFormatted }}
                            </td>

                            {{-- Voucher --}}
                            <td>
                                <span class="badge bg-light text-primary border font-monospace" style="font-size: 10px;">
                                    {{ $row->voucher_no ?? ('SAL-' . $row->id) }}
                                </span>
                            </td>

                            {{-- Days --}}
                            <td class="text-center font-monospace">
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 10px;">
                                    {{ $row->worked_days }} / {{ $row->working_days ?? 30 }}
                                </span>
                            </td>

                            {{-- Basic --}}
                            <td class="text-end font-monospace text-muted" style="font-size: 11px;">
                                ৳{{ number_format((float)$row->basic_salary, 2) }}
                            </td>

                            {{-- Bonus --}}
                            <td class="text-end font-monospace text-success" style="font-size: 11px;">
                                +৳{{ number_format((float)$row->bonus, 2) }}
                            </td>

                            {{-- Deduction --}}
                            <td class="text-end font-monospace text-danger" style="font-size: 11px;">
                                -৳{{ number_format((float)$row->deduction, 2) }}
                            </td>

                            {{-- Net Salary --}}
                            <td class="text-end font-monospace fw-bold text-dark" style="font-size: 12px;">
                                ৳{{ number_format((float)$row->net_salary, 2) }}
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($isPaid)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 9.5px;">
                                        <i class="fas fa-check-circle me-1"></i> Paid
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 9.5px;">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Payment Account --}}
                            <td>
                                @if(!empty($row->bank_name))
                                    <small class="text-dark font-monospace d-block" style="font-size: 10.5px;">
                                        <i class="fas fa-building-columns text-secondary me-1"></i>
                                        {{ $row->bank_name }}
                                    </small>

                                    @if(!empty($row->account_number))
                                        <small class="text-muted font-monospace" style="font-size: 9.5px;">
                                            A/C: {{ $row->account_number }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                @endif
                            </td>

                            {{-- Payment Date --}}
                            <td class="font-monospace" style="font-size: 10.5px;">
                                @if(!empty($row->paid_at))
                                    <span class="text-dark d-block">
                                        {{ date('d M Y', strtotime($row->paid_at)) }}
                                    </span>

                                    <small class="text-muted" style="font-size: 9.5px;">
                                        By: {{ $row->disburser_name ?? 'Admin' }}
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Receipt --}}
                            <td class="text-end pe-3">
                                @if($isPaid)
                                    <a href="{{ route('admin.payroll.payslip', $row->id) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-light border btn-compact shadow-xs"
                                       title="Print Salary Slip">
                                        <i class="fas fa-print text-primary"></i>
                                        Slip
                                    </a>
                                @else
                                    <span class="text-muted font-monospace" style="font-size: 10px;">
                                        Pending
                                    </span>
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="11" class="text-center empty-state text-muted small">
                                <i class="fas fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                                <span class="fw-semibold">
                                    No salary records found.
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>
@endsection