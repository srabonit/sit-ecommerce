<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Closing Summary - #{{ $session->id ?? '' }}</title>

    <style>
        :root {
            --slip-text: #172033;
            --slip-muted: #64748b;
            --slip-border: #cbd5e1;
            --slip-soft: #f1f5f9;
            --slip-primary: #1e293b;
            --slip-success: #15803d;
            --slip-danger: #dc2626;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #eef2f7;
            color: var(--slip-text);
            font-size: 12px;
            line-height: 1.45;
            padding: 24px 12px;
        }

        .action-bar {
            width: 100%;
            max-width: 380px;
            margin: 0 auto 14px;
            display: flex;
            gap: 8px;
        }

        .btn-print,
        .btn-close {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 12px;
            border: 1px solid transparent;
            border-radius: 5px;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-print {
            flex: 1;
            color: #fff;
            background: var(--slip-primary);
        }

        .btn-print:hover {
            background: #334155;
        }

        .btn-close {
            color: #334155;
            background: #fff;
            border-color: var(--slip-border);
        }

        .btn-close:hover {
            background: var(--slip-soft);
        }

        .print-container {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            padding: 18px 15px;
            background: #fff;
            border: 1px solid var(--slip-border);
            border-radius: 6px;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.07);
        }

        .text-center {
            text-align: center;
        }

        .text-start {
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: 700;
        }

        .text-muted {
            color: var(--slip-muted);
        }

        .text-danger {
            color: var(--slip-danger);
        }

        .text-success {
            color: var(--slip-success);
        }

        .branch-name {
            margin: 0 0 3px;
            font-size: 16px;
            line-height: 1.3;
            font-weight: 800;
            text-transform: uppercase;
            overflow-wrap: anywhere;
        }

        .branch-meta {
            margin: 2px 0;
            font-size: 10px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .slip-heading {
            margin: 9px 0 4px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.7px;
        }

        .session-meta {
            margin: 0;
            font-size: 10px;
        }

        .divider {
            margin: 9px 0;
            border-top: 1px dashed #94a3b8;
        }

        .divider-double {
            margin: 9px 0;
            border-top: 2px dashed var(--slip-primary);
        }

        .slip-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
        }

        .slip-table td {
            padding: 3px 0;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .slip-table td:first-child {
            width: 65%;
            padding-right: 5px;
        }

        .slip-table td:last-child {
            width: 35%;
            text-align: right;
            white-space: normal;
        }

        .highlight-row {
            background: #f1f5f9;
            font-weight: 700;
        }

        .highlight-row td {
            padding: 6px 4px;
        }

        .highlight-row td:last-child {
            font-size: 12px;
        }

        .variance-row td {
            padding-top: 5px;
            padding-bottom: 5px;
            font-size: 13px;
            font-weight: 800;
        }

        .variance-label small {
            display: block;
            margin-top: 2px;
            font-size: 9px;
            font-weight: 400;
        }

        .signature-box {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 48px;
            padding-top: 5px;
        }

        .sign-line {
            width: 50%;
            padding-top: 4px;
            border-top: 1px solid #64748b;
            text-align: center;
            font-size: 9px;
            font-weight: 700;
        }

        .remarks {
            margin: 0;
            font-size: 10px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .slip-footer {
            margin: 0;
            font-size: 9px;
            line-height: 1.5;
        }

        @media (max-width: 420px) {
            body {
                padding: 12px 8px;
            }

            .print-container {
                padding: 15px 12px;
            }
        }

        @media print {
            html,
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            body {
                font-size: 11px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .action-bar {
                display: none !important;
            }

            .print-container {
                width: 78mm;
                max-width: 78mm;
                margin: 0;
                padding: 4px 2px;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }

            .highlight-row {
                background: #f1f5f9 !important;
            }

            .text-danger {
                color: #000 !important;
            }

            .text-success {
                color: #000 !important;
            }

            @page {
                size: 80mm auto;
                margin: 2mm;
            }
        }
    </style>
</head>

<body>

    <!-- On-screen Action Bar -->
    <div class="action-bar">
        <button type="button" class="btn-print" onclick="window.print()">
            <span aria-hidden="true">🖨</span>
            Print Closing Slip
        </button>

        <a href="javascript:window.close()" class="btn-close">
            Close
        </a>
    </div>

    <div class="print-container">

        <!-- Branch Header -->
        <div class="text-center">
            <h2 class="branch-name">
                {{ $session->branch_name ?? 'BRANCH' }}
            </h2>

            @if(!empty($session->branch_code))
                <p class="branch-meta text-muted">
                    Hub Code: {{ $session->branch_code }}
                </p>
            @endif

            @if(!empty($session->branch_address))
                <p class="branch-meta">
                    {{ $session->branch_address }}
                </p>
            @endif

            @if(!empty($session->branch_phone))
                <p class="branch-meta">
                    Tel: {{ $session->branch_phone }}
                </p>
            @endif

            <div class="divider"></div>

            <h3 class="slip-heading">
                DAILY CASH CLOSING SLIP
            </h3>

            <p class="session-meta">
                Session ID: #{{ $session->id ?? '' }}
                |
                Status: {{ strtoupper($session->status ?? 'CLOSED') }}
            </p>
        </div>

        <div class="divider"></div>

        <!-- Shift & Time Meta -->
        <table class="slip-table">
            <tr>
                <td class="text-muted">Closing Date:</td>
                <td class="fw-bold">
                    {{ !empty($session->closing_date) ? date('d M Y', strtotime($session->closing_date)) : date('d M Y') }}
                </td>
            </tr>

            <tr>
                <td class="text-muted">Cashier / Staff:</td>
                <td class="fw-bold">
                    {{ $session->cashier_name ?? ($session->user_name ?? 'N/A') }}
                </td>
            </tr>

            @if(!empty($session->opened_at))
                <tr>
                    <td class="text-muted">Shift Opened:</td>
                    <td>
                        {{ date('h:i A', strtotime($session->opened_at)) }}
                    </td>
                </tr>
            @endif

            @if(!empty($session->closed_at))
                <tr>
                    <td class="text-muted">Shift Closed:</td>
                    <td>
                        {{ date('h:i A', strtotime($session->closed_at)) }}
                    </td>
                </tr>
            @endif

            @if(!empty($session->verified_name))
                <tr>
                    <td class="text-muted">Verified By:</td>
                    <td class="fw-bold">
                        {{ $session->verified_name }}
                    </td>
                </tr>
            @endif
        </table>

        <div class="divider"></div>

        <!-- Cash Drawer Reconciliation Breakdown -->
        <table class="slip-table">
            <tr>
                <td>(+) Opening Cash:</td>
                <td>
                    ৳{{ number_format($session->opening_cash ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td>(+) POS Cash Sales:</td>
                <td class="text-success">
                    +৳{{ number_format($session->pos_cash_sales ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td>(+) Due Collected (Cash):</td>
                <td class="text-success">
                    +৳{{ number_format($session->cash_due_collection ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td>(+) Cash In / Deposits:</td>
                <td class="text-success">
                    +৳{{ number_format($session->cash_in ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td>(-) Cash Expenses (Shop):</td>
                <td class="text-danger">
                    -৳{{ number_format($session->cash_expenses ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td>(-) Customer Cash Refunds:</td>
                <td class="text-danger">
                    -৳{{ number_format($session->cash_refunds ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td>(-) Cash Out / HQ Handover:</td>
                <td class="text-danger">
                    -৳{{ number_format($session->cash_out ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <div class="divider"></div>
                </td>
            </tr>

            <!-- Expected vs Actual -->
            <tr class="highlight-row">
                <td>Expected Cash in Drawer:</td>
                <td>
                    ৳{{ number_format($session->expected_cash ?? 0, 2) }}
                </td>
            </tr>

            <tr class="highlight-row">
                <td>Actual Physical Cash Count:</td>
                <td>
                    ৳{{ number_format($session->actual_cash ?? 0, 2) }}
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <div class="divider-double"></div>
                </td>
            </tr>

            <!-- Difference / Variance -->
            @php
                $diff = (float) ($session->difference ?? 0);
            @endphp

            <tr class="variance-row">
                <td class="variance-label">
                    Discrepancy:

                    <small>
                        @if($diff < 0)
                            (Cash Shortage)
                        @elseif($diff > 0)
                            (Cash Surplus / Excess)
                        @else
                            (Balanced)
                        @endif
                    </small>
                </td>

                <td class="{{ $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : '') }}">
                    {{ $diff >= 0 ? '+' : '' }}৳{{ number_format($diff, 2) }}
                </td>
            </tr>
        </table>

        <div class="divider-double"></div>

        <!-- Sales Metrics Summary -->
        <table class="slip-table">
            <tr>
                <td class="text-muted">Total POS Orders:</td>
                <td class="fw-bold">
                    {{ $session->total_orders_count ?? 0 }}
                </td>
            </tr>

            <tr>
                <td class="text-muted">Digital Sales (bKash/Nagad/Card):</td>
                <td class="fw-bold">
                    ৳{{ number_format($session->digital_sales_amount ?? 0, 2) }}
                </td>
            </tr>

            <tr class="highlight-row">
                <td>Total Sales Turnover (All Channels):</td>
                <td>
                    ৳{{ number_format($session->total_sales_amount ?? 0, 2) }}
                </td>
            </tr>
        </table>

        @if(!empty($session->notes))
            <div class="divider"></div>

            <p class="remarks">
                <strong>Remarks:</strong>
                {{ $session->notes }}
            </p>
        @endif

        <!-- Signature Section -->
        <div class="signature-box">
            <div class="sign-line">
                Cashier Signature
            </div>

            <div class="sign-line">
                Manager / Auditor
            </div>
        </div>

        <div class="divider" style="margin-top: 25px;"></div>

        <p class="slip-footer text-center text-muted">
            Generated on {{ date('d-m-Y h:i A') }}
            |
            Powered by Multi-Branch POS
        </p>

    </div>

    <script>
        // Auto trigger print dialog on page load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>

</body>
</html>