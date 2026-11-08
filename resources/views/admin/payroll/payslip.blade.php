<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Salary Slip #{{ $payroll->voucher_no ?? $payroll->id }} - {{ config('app.name', 'Shop Now BD') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --sl-primary: var(--bs-primary, #1572e8);
            --sl-success: var(--bs-success, #31ce36);
            --sl-danger: var(--bs-danger, #f25961);
            --sl-text: #344054;
            --sl-heading: #1d2939;
            --sl-muted: #667085;
            --sl-border: #e7eaf0;
            --sl-light: #f7f8fa;
            --sl-success-bg: #ecfdf3;
            --sl-success-text: #067647;
            --sl-warning-bg: #fffaeb;
            --sl-warning-text: #b54708;
        }

        * {
            box-sizing: border-box;
        }

        html {
            -webkit-text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: #f5f7fb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--sl-text);
            font-size: 13px;
            line-height: 1.55;
        }

        .font-monospace {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-weight: 600 !important;
        }

        .payslip-container {
            position: relative;
            isolation: isolate;
            width: calc(100% - 32px);
            max-width: 820px;
            margin: 20px auto 32px;
            padding: 34px 38px;
            background: #fff;
            border: 1px solid var(--sl-border);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(16, 24, 40, .05);
            overflow: hidden;
        }

        .payslip-content {
            position: relative;
            z-index: 2;
        }

        .payslip-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: clamp(55px, 10vw, 85px);
            font-weight: 900;
            color: rgba(22, 163, 74, .07);
            letter-spacing: 6px;
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
        }

        /* Action Toolbar */
        .action-bar {
            width: calc(100% - 32px);
            max-width: 820px;
            margin: 20px auto 0;
            padding: 0;
        }

        .action-toolbar {
            min-height: 52px;
            padding: 9px 12px;
            background: #fff;
            border: 1px solid var(--sl-border);
            border-radius: 7px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .03);
        }

        .btn-compact {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 34px;
            padding: 7px 13px !important;
            border-radius: 5px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1.4;
            white-space: nowrap;
            transition: all .2s ease;
        }

        /* Header */
        .payslip-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--sl-heading);
        }

        .company-name {
            font-size: 23px;
            font-weight: 800;
            color: var(--sl-heading);
            letter-spacing: -.5px;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .company-name i {
            flex-shrink: 0;
        }

        .company-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: var(--sl-muted);
            font-weight: 500;
        }

        .voucher-info {
            min-width: 180px;
            text-align: right;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 11px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
        }

        .status-paid {
            background: var(--sl-success-bg);
            color: var(--sl-success-text);
            border: 1px solid #abefc6;
        }

        .status-pending {
            background: var(--sl-warning-bg);
            color: var(--sl-warning-text);
            border: 1px solid #fedf89;
        }

        /* Staff Details */
        .section-label {
            display: block;
            margin-bottom: 5px;
            color: var(--sl-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .staff-details {
            padding: 17px;
            margin-bottom: 22px;
            background: var(--sl-light);
            border: 1px solid var(--sl-border);
            border-radius: 7px;
        }

        .detail-value {
            display: block;
            color: var(--sl-heading);
            font-size: 13px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .detail-sub {
            display: block;
            margin-top: 4px;
            color: var(--sl-muted);
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        /* Salary Table */
        .payslip-table {
            width: 100%;
            margin-bottom: 22px;
            border-collapse: collapse;
        }

        .payslip-table th {
            padding: 12px 11px;
            background: #f2f4f7;
            color: #475467;
            border-top: 1px solid var(--sl-border);
            border-bottom: 1px solid #d0d5dd;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .payslip-table td {
            padding: 13px 11px;
            border-bottom: 1px solid var(--sl-border);
            color: var(--sl-text);
            font-size: 12px;
            vertical-align: middle;
        }

        .payslip-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .component-title {
            display: block;
            color: var(--sl-heading);
            font-size: 12px;
            font-weight: 700;
        }

        .component-note {
            display: block;
            margin-top: 3px;
            color: var(--sl-muted);
            font-size: 10px;
        }

        .net-total-row {
            background: #ecfdf3;
            border-top: 1px solid #abefc6;
        }

        .net-total-label {
            color: #067647;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .net-total-value {
            color: #039855;
            font-size: 18px;
            font-weight: 800;
            white-space: nowrap;
        }

        /* Payment Details */
        .payment-details {
            padding: 16px 17px;
            margin-bottom: 20px;
            background: var(--sl-light);
            border: 1px solid var(--sl-border);
            border-radius: 7px;
        }

        .payment-value {
            color: var(--sl-heading);
            font-size: 12px;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        /* Signatures */
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-top: 65px;
            padding-top: 10px;
        }

        .signature-item {
            flex: 1;
            min-width: 0;
            text-align: center;
        }

        .signature-line {
            width: 85%;
            max-width: 175px;
            margin: 0 auto 8px;
            border-top: 1px dashed #98a2b3;
        }

        .signature-label {
            color: #475467;
            font-size: 11px;
            font-weight: 600;
        }

        .footer-note {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid var(--sl-border);
            color: var(--sl-muted);
            font-size: 10px;
            text-align: center;
        }

        /* Tablet */
        @media (max-width: 767.98px) {
            .action-bar {
                width: calc(100% - 24px);
                margin-top: 12px;
            }

            .action-toolbar {
                gap: 8px;
                flex-wrap: wrap;
            }

            .payslip-container {
                width: calc(100% - 24px);
                margin: 12px auto 24px;
                padding: 24px 20px;
            }

            .company-name {
                font-size: 19px;
            }

            .company-subtitle {
                font-size: 11px;
            }

            .voucher-info {
                min-width: 145px;
            }

            .payslip-watermark {
                font-size: 55px;
            }

            .staff-details {
                padding: 13px;
            }

            .payslip-table th,
            .payslip-table td {
                padding: 10px 8px;
            }

            .signature-section {
                margin-top: 50px;
                gap: 12px;
            }
        }

        /* Mobile */
        @media (max-width: 480px) {
            body {
                font-size: 12px;
            }

            .action-bar {
                width: calc(100% - 20px);
            }

            .action-toolbar {
                padding: 8px;
            }

            .btn-compact {
                padding: 7px 10px !important;
                font-size: 11px !important;
            }

            .payslip-container {
                width: calc(100% - 20px);
                padding: 19px 13px;
                border-radius: 6px;
            }

            .payslip-header {
                gap: 10px;
                padding-bottom: 15px;
                margin-bottom: 15px;
            }

            .company-name {
                gap: 6px !important;
                font-size: 16px;
            }

            .company-subtitle {
                font-size: 10px;
            }

            .voucher-info {
                min-width: 112px;
            }

            .status-badge {
                padding: 5px 7px;
                font-size: 9px;
            }

            .voucher-info .small {
                font-size: 9px !important;
            }

            .staff-details {
                padding: 12px 10px;
                margin-bottom: 16px;
            }

            .detail-value {
                font-size: 11px;
            }

            .detail-sub {
                font-size: 10px;
            }

            .section-label {
                font-size: 9px;
            }

            .payslip-table {
                min-width: 570px;
            }

            .payslip-table th,
            .payslip-table td {
                padding: 9px 7px;
            }

            .component-title {
                font-size: 11px;
            }

            .component-note {
                font-size: 9px;
            }

            .net-total-value {
                font-size: 14px;
            }

            .payment-details {
                padding: 13px 11px;
            }

            .payment-value {
                font-size: 11px;
            }

            .signature-section {
                margin-top: 40px;
                gap: 7px;
            }

            .signature-label {
                font-size: 9px;
            }

            .footer-note {
                font-size: 9px;
            }
        }

        /* Small Mobile */
        @media (max-width: 360px) {
            .payslip-header {
                flex-direction: column;
            }

            .voucher-info {
                width: 100%;
                min-width: 0;
                text-align: left;
            }

            .company-name {
                font-size: 17px;
            }

            .signature-section {
                gap: 4px;
            }

            .signature-label {
                font-size: 8px;
            }
        }

        /* Print */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            html,
            body {
                width: 100%;
                min-height: 0;
                background: #fff !important;
                color: #000 !important;
                font-size: 10px;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .payslip-container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10px 12px !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .payslip-header,
            .staff-details,
            .payslip-table,
            .payment-details,
            .signature-section {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .payslip-table {
                min-width: 0 !important;
            }

            .payslip-table th,
            .payslip-table td {
                padding: 7px 8px !important;
            }

            .payslip-watermark {
                color: rgba(22, 163, 74, .07) !important;
            }

            .signature-section {
                margin-top: 45px !important;
            }

            .footer-note {
                margin-top: 15px;
            }
        }
    </style>
</head>

<body>

@php
    $monthFormatted = \Carbon\Carbon::createFromDate($payroll->salary_year, $payroll->salary_month, 1)->format('F Y');
    $isPaid = $payroll->status === 'paid';
    $siteName = config('app.name', 'Shop Now BD');
@endphp

{{-- Action Bar --}}
<div class="container action-bar no-print">
    <div class="action-toolbar d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.payroll.index', ['year' => $payroll->salary_year, 'month' => $payroll->salary_month]) }}"
           class="btn btn-sm btn-outline-secondary btn-compact">
            <i class="fas fa-arrow-left"></i>
            Salary Sheet
        </a>

        <button onclick="window.print()" class="btn btn-sm btn-primary btn-compact px-3">
            <i class="fas fa-print"></i>
            Print Payslip
        </button>
    </div>
</div>

{{-- Payslip --}}
<div class="payslip-container">

    @if($isPaid)
        <div class="payslip-watermark">PAID</div>
    @endif

    <div class="payslip-content">

        {{-- Company Header --}}
        <div class="payslip-header">
            <div>
                <h4 class="company-name mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-bag-shopping text-primary"></i>
                    <span>{{ $siteName }}</span>
                </h4>

                <small class="company-subtitle d-block">
                    Staff Salary &amp; Allowance Slip
                </small>

                <small class="text-secondary font-monospace d-block" style="font-size: 9px;">
                    E-commerce &amp; Retail Management
                </small>
            </div>

            <div class="voucher-info">
                <span class="status-badge {{ $isPaid ? 'status-paid' : 'status-pending' }}">
                    <i class="fas {{ $isPaid ? 'fa-check-circle' : 'fa-clock' }} me-1"></i>
                    {{ $isPaid ? 'PAID' : 'PENDING' }}
                </span>

                <div class="font-monospace text-muted mt-2" style="font-size: 10px;">
                    Voucher:
                    <strong class="text-dark">
                        {{ $payroll->voucher_no ?? ('SAL-' . $payroll->id) }}
                    </strong>
                </div>

                <div class="font-monospace text-muted" style="font-size: 10px;">
                    Date:
                    <strong class="text-dark">{{ date('d M Y') }}</strong>
                </div>
            </div>
        </div>

        {{-- Staff Details --}}
        <div class="staff-details">
            <div class="row g-3">

                <div class="col-6 col-md-3">
                    <span class="section-label">Staff Name</span>
                    <strong class="detail-value">{{ $payroll->staff_name }}</strong>
                    <small class="detail-sub font-monospace">
                        ID: #{{ $payroll->user_id }}
                    </small>
                </div>

                <div class="col-6 col-md-3">
                    <span class="section-label">Designation</span>
                    <strong class="detail-value font-monospace">
                        {{ ucfirst($payroll->staff_role ?? 'Staff Member') }}
                    </strong>
                    <small class="detail-sub">Department: Operations</small>
                </div>

                <div class="col-6 col-md-3">
                    <span class="section-label">Payroll Month</span>
                    <strong class="detail-value text-primary font-monospace">
                        {{ $monthFormatted }}
                    </strong>
                    <small class="detail-sub font-monospace">
                        Cycle: 1st–30th
                    </small>
                </div>

                <div class="col-6 col-md-3">
                    <span class="section-label">Joining Date</span>
                    <strong class="detail-value font-monospace">
                        {{ !empty($payroll->joining_date) ? date('d M Y', strtotime($payroll->joining_date)) : 'N/A' }}
                    </strong>
                    <small class="detail-sub font-monospace">
                        Worked: {{ $payroll->worked_days }} / {{ $payroll->working_days ?? 30 }} Days
                    </small>
                </div>

            </div>
        </div>

        {{-- Earnings & Deductions --}}
        <div class="table-responsive">
            <table class="table payslip-table">

                <thead>
                    <tr>
                        <th>Salary Details</th>
                        <th>Calculation</th>
                        <th class="text-end">Earnings (BDT)</th>
                        <th class="text-end">Deduction (BDT)</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- Basic Salary --}}
                    <tr>
                        <td>
                            <strong class="component-title">Basic Salary</strong>
                            <small class="component-note">
                                Monthly salary
                            </small>
                        </td>

                        <td class="font-monospace text-muted" style="font-size: 10px;">
                            ৳{{ number_format(round((float)$payroll->basic_salary / 30, 2), 2) }}
                            &times; {{ $payroll->worked_days }} Days
                        </td>

                        <td class="text-end font-monospace text-dark">
                            ৳{{ number_format(round(((float)$payroll->basic_salary / 30) * (int)$payroll->worked_days, 2), 2) }}
                        </td>

                        <td class="text-end font-monospace text-muted">—</td>
                    </tr>

                    {{-- Bonus --}}
                    @if((float)$payroll->bonus > 0)
                        <tr>
                            <td>
                                <strong class="component-title text-success">
                                    Bonus / Incentive
                                </strong>
                                <small class="component-note">
                                    Approved bonus
                                </small>
                            </td>

                            <td class="text-muted">Addition</td>

                            <td class="text-end font-monospace text-success">
                                +৳{{ number_format((float)$payroll->bonus, 2) }}
                            </td>

                            <td class="text-end font-monospace text-muted">—</td>
                        </tr>
                    @endif

                    {{-- Deduction --}}
                    @if((float)$payroll->deduction > 0)
                        <tr>
                            <td>
                                <strong class="component-title text-danger">
                                    Salary Deduction
                                </strong>
                                <small class="component-note">
                                    Absence / Late / Advance
                                </small>
                            </td>

                            <td class="text-muted">Deduction</td>

                            <td class="text-end font-monospace text-muted">—</td>

                            <td class="text-end font-monospace text-danger">
                                -৳{{ number_format((float)$payroll->deduction, 2) }}
                            </td>
                        </tr>
                    @endif

                    {{-- Net Salary --}}
                    <tr class="net-total-row">
                        <td colspan="2" class="net-total-label">
                            NET SALARY
                        </td>

                        <td colspan="2" class="text-end">
                            <span class="net-total-value font-monospace">
                                ৳ {{ number_format((float)$payroll->net_salary, 2) }}
                            </span>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- Payment Details --}}
        <div class="payment-details">
            <div class="row g-3 align-items-center">

                <div class="col-12 col-md-8">
                    <span class="section-label">
                        Payment Details
                    </span>

                    <div class="payment-value font-monospace">
                        <i class="fas fa-building-columns text-primary me-1"></i>

                        {{ $payroll->bank_name ?? 'Default Cash / Bank Account' }}

                        @if(!empty($payroll->account_number))
                            (A/C: {{ $payroll->account_number }})
                        @endif

                        <span class="text-muted">|</span>

                        Method:
                        <span class="text-uppercase">
                            {{ $payroll->payment_method ?? 'Bank Transfer' }}
                        </span>
                    </div>

                    @if(!empty($payroll->notes))
                        <small class="text-muted d-block mt-2 font-monospace" style="font-size: 9.5px;">
                            <i class="fas fa-note-sticky me-1"></i>
                            Note: {{ $payroll->notes }}
                        </small>
                    @endif
                </div>

                <div class="col-12 col-md-4 text-md-end">
                    <span class="section-label">
                        Payment Date
                    </span>

                    <span class="font-monospace text-dark fw-bold" style="font-size: 10.5px;">
                        {{ !empty($payroll->paid_at) ? date('d M Y, h:i A', strtotime($payroll->paid_at)) : 'Pending' }}
                    </span>

                    @if(!empty($payroll->disburser_name))
                        <small class="text-muted d-block font-monospace" style="font-size: 9px;">
                            By: {{ $payroll->disburser_name }}
                        </small>
                    @endif
                </div>

            </div>
        </div>

        {{-- Signatures --}}
        <div class="signature-section">

            <div class="signature-item">
                <div class="signature-line"></div>
                <span class="signature-label">Employee</span>
            </div>

            <div class="signature-item">
                <div class="signature-line"></div>
                <span class="signature-label">Accounts</span>
            </div>

            <div class="signature-item">
                <div class="signature-line"></div>
                <span class="signature-label">Managing Director</span>
            </div>

        </div>

        <div class="footer-note">
            Computer-generated salary payslip.
        </div>

    </div>
</div>

</body>
</html>