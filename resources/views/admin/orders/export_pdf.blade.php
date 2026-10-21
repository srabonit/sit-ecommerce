@php
    // 1. Fetch site branding settings and favicon from database
    $siteSettings = \Illuminate\Support\Facades\DB::table('settings')
        ->whereIn('key', ['footer_brand_title', 'footer_phone', 'footer_email', 'header_logo', 'footer_logo', 'site_icon', 'favicon'])
        ->pluck('value', 'key')
        ->toArray();

    $siteName = !empty($siteSettings['footer_brand_title']) 
        ? $siteSettings['footer_brand_title'] 
        : config('app.name', 'Shop Now BD');

    // Logo image resolution with type-safe fallback
    $siteLogo = null;
    $rawLogo = $siteSettings['header_logo'] ?? ($siteSettings['footer_logo'] ?? null);
    if (!empty($rawLogo)) {
        if (class_exists(\App\Services\ImagePathService::class)) {
            $resolved = \App\Services\ImagePathService::resolveUrl($rawLogo, '');
            $siteLogo = !empty($resolved) ? $resolved : null;
        } elseif (function_exists('show_image')) {
            $siteLogo = show_image($rawLogo, '');
        } elseif (filter_var($rawLogo, FILTER_VALIDATE_URL)) {
            $siteLogo = $rawLogo;
        } else {
            $siteLogo = asset(ltrim($rawLogo, '/'));
        }
    }

    // Dynamic browser tab favicon resolution[cite: 12, 13]
    $dbSiteIcon = $siteSettings['site_icon'] ?? ($siteSettings['favicon'] ?? null);
    $finalFavicon = null;
    if (!empty($dbSiteIcon)) {
        if (filter_var($dbSiteIcon, FILTER_VALIDATE_URL)) {
            $finalFavicon = $dbSiteIcon;
        } else {
            $cleanIcon = ltrim(str_replace(['storage/', 'public/'], '', $dbSiteIcon), '/');
            if (file_exists(public_path($cleanIcon))) {
                $finalFavicon = asset($cleanIcon);
            } elseif (file_exists(public_path('uploads/' . $cleanIcon))) {
                $finalFavicon = asset('uploads/' . $cleanIcon);
            } else {
                $finalFavicon = asset($cleanIcon);
            }
        }
    }
    if (empty($finalFavicon) && file_exists(public_path('favicon.ico'))) {
        $finalFavicon = asset('favicon.ico');
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders_Report_{{ date('Y_m_d_His') }} - {{ config('app.name') }}</title>
    
    <!-- Dynamic Site Favicon for Browser Tab -->
    @if(!empty($finalFavicon))
        <link rel="icon" type="image/x-icon" href="{{ $finalFavicon }}">
        <link rel="shortcut icon" href="{{ $finalFavicon }}">
        <link rel="apple-touch-icon" href="{{ $finalFavicon }}">
    @endif

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- html2pdf Client-side Auto Downloader -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        /* Strict 5px Element Radius Across Controls */
        .report-control-ui .btn,
        .report-control-ui .badge,
        .report-control-ui .modal-content,
        .report-control-ui .filter-badges-bar,
        .report-control-ui .print-container,
        .report-control-ui .no-print-toolbar,
        .report-control-ui .scroll-nav-btn,
        .report-control-ui .mobile-scroll-dock {
            border-radius: 5px !important;
        }

        /* Compact Button Styling */
        .btn-compact {
            font-size: 11.5px !important;
            padding: 5px 12px !important;
            line-height: 1.3 !important;
            font-weight: 600 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        /* A4 Landscape Page Configuration */
        @page {
            size: A4 landscape;
            margin: 5mm 6mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
            background: #0f172a;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 9.5px;
            line-height: 1.35;
        }

        /* Top Action Toolbar */
        .no-print-toolbar {
            background: #020617;
            border-bottom: 1px solid #1e293b;
            padding: 10px 16px;
            position: sticky;
            top: 0;
            z-index: 1050;
            width: 100%;
        }

        .toolbar-inner {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-primary { background-color: #2563eb; color: #ffffff; }
        .btn-primary:hover { background-color: #1d4ed8; }

        .btn-success { background-color: #16a34a; color: #ffffff; }
        .btn-success:hover { background-color: #15803d; }

        .btn-warning { background-color: #d97706; color: #ffffff; }
        .btn-warning:hover { background-color: #b45309; }

        .btn-light { background-color: #1e293b; color: #f8fafc; border-color: #334155; }
        .btn-light:hover { background-color: #334155; color: #ffffff; }

        /* Responsive Mobile Toolbar */
        @media (max-width: 650px) {
            .no-print-toolbar {
                padding: 8px 10px;
            }
            .toolbar-inner {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .toolbar-left {
                justify-content: space-between;
                width: 100%;
            }
            .toolbar-right {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }
            .toolbar-right .btn {
                width: 100%;
                padding: 7px 6px;
                font-size: 11px;
            }
        }

        /* Report Canvas Stage Architecture */
        .report-stage {
            width: 100%;
            padding: 20px 10px 80px;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            scroll-behavior: smooth;
        }

        .report-scale-wrapper {
            transition: transform 0.15s ease;
            flex-shrink: 0;
            margin: 0 auto;
        }

        /* Fixed Standard A4 Landscape Canvas: 1080px */
        .print-container {
            width: 1080px !important;
            min-width: 1080px !important;
            max-width: 1080px !important;
            background: #ffffff;
            padding: 18px 22px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
        }

        /* Desktop View Tuning (>= 992px): Perfectly Centered */
        @media (min-width: 992px) {
            .report-stage {
                justify-content: center !important;
                padding: 24px 16px 60px !important;
            }
            .report-scale-wrapper {
                transform: none !important;
                width: 1080px;
                margin: 0 auto !important;
            }
            #btnToggleZoom,
            .mobile-scroll-dock {
                display: none !important;
            }
        }

        /* Mobile View (< 992px): Left-to-Right Horizontal Scrolling Support */
        @media (max-width: 991.98px) {
            .report-stage {
                justify-content: flex-start;
            }
            .report-scale-wrapper {
                transform-origin: top left;
                margin: 0;
            }
        }

        /* Header Grid */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 8px;
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 10px;
            gap: 12px;
        }

        .brand-logo-img {
            max-height: 36px;
            max-width: 140px;
            object-fit: contain;
            display: block;
            margin-bottom: 4px;
        }

        .store-brand h2 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }
        .store-brand p {
            margin: 2px 0 0 0;
            color: #64748b;
            font-size: 9px;
        }

        .meta-info {
            text-align: right;
            font-size: 9.5px;
            color: #334155;
            flex-shrink: 0;
        }

        /* Filter Badges Bar */
        .filter-badges-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            padding: 6px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
            font-size: 9px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-weight: 700;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .badge-dark    { background: #1e293b; color: #ffffff; }
        .badge-info    { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-warn    { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }

        /* Orders Data Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }

        .orders-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            vertical-align: middle;
        }

        .orders-table td {
            padding: 5px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .orders-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        /* Bottom Summary Tfoot */
        .orders-table tfoot td {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            padding: 7px 6px;
            font-weight: 700;
            font-size: 9.5px;
        }

        .font-mono,
        .bn-digit {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700 !important;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        /* Floating Horizontal Quick Scroll Bar for Mobile */
        .mobile-scroll-dock {
            position: fixed;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1045;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(6px);
            padding: 5px 8px;
            border: 1px solid #334155;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            display: none;
            gap: 5px;
            align-items: center;
        }

        .scroll-nav-btn {
            background: #1e293b;
            color: #f8fafc;
            border: 1px solid #475569;
            font-size: 10.5px;
            font-weight: 600;
            padding: 5px 9px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            transition: all 0.12s ease;
        }
        .scroll-nav-btn:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        /* Custom Modal Backdrop */
        .custom-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .custom-modal-content {
            background: #ffffff;
            padding: 16px;
            max-width: 320px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        /* Print Media Overrides */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print-toolbar,
            .mobile-scroll-dock,
            .custom-modal-backdrop {
                display: none !important;
            }
            .report-stage {
                padding: 0 !important;
                background: transparent !important;
                overflow: visible !important;
                display: block !important;
                height: auto !important;
            }
            .report-scale-wrapper {
                transform: none !important;
                margin: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                min-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
            .orders-table th {
                background-color: #e2e8f0 !important;
            }
        }
    </style>
</head>
<body class="report-control-ui">

    <!-- Action Toolbar -->
    <div class="no-print-toolbar">
        <div class="toolbar-inner">
            <div class="toolbar-left">
                <strong style="color: #f8fafc; font-size: 13px;">
                    <i class="fas fa-file-pdf text-danger me-1"></i> Orders Export Preview
                </strong>
                <span class="badge badge-dark bn-digit font-mono" style="font-size: 10px;">
                    {{ $exportOrders->count() }} Orders
                </span>
            </div>

            <div class="toolbar-right">
                <!-- Mobile Zoom / Fit Screen Toggle -->
                <button type="button" class="btn btn-warning d-lg-none" id="btnToggleZoom" onclick="toggleFitScreen()">
                    <i class="fas fa-arrows-left-right me-1"></i> <span id="zoomToggleText">Fit Screen</span>
                </button>

                <!-- Direct Print Dialog -->
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>

                <!-- Direct 1-Click A4 Landscape PDF Download -->
                <button type="button" class="btn btn-success" id="btn-download-pdf" onclick="downloadPdfDirectly()">
                    <i class="fas fa-download"></i> <span id="btn-pdf-text">Download PDF</span>
                </button>

                <button type="button" class="btn btn-light" onclick="window.close()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Scalable Report Stage (Centered on PC, Left-Scrollable on Mobile) -->
    <div class="report-stage" id="reportStage">
        <div class="report-scale-wrapper" id="reportScaleWrapper">
            <div class="print-container" id="printable-order-report">
                <!-- Header Grid -->
                <div class="report-header">
                    <div class="store-brand">
                        @if(!empty($siteLogo))
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="brand-logo-img" id="reportHeaderLogo" onerror="this.style.display='none'; document.getElementById('reportHeaderTitle').style.display='block';">
                            <h2 id="reportHeaderTitle" style="display: none;">{{ $siteName }}</h2>
                        @else
                            <h2 id="reportHeaderTitle">{{ $siteName }}</h2>
                        @endif
                        <p>Orders Summary Report &bull; Printed: <span class="bn-digit">{{ date('d M Y, h:i A') }}</span></p>
                    </div>
                    <div class="meta-info">
                        <div>Total Orders: <strong class="bn-digit font-mono">{{ $exportOrders->count() }}</strong> &bull; Currency: <strong>BDT (৳)</strong></div>
                    </div>
                </div>

                <!-- Filter Badges -->
                <div class="filter-badges-bar">
                    <strong style="color: #475569;">Filter:</strong>
                    
                    <span class="badge badge-dark">
                        Date: {{ strtoupper(str_replace('_', ' ', $request->get('date_filter', 'All Time'))) }}
                    </span>

                    @if($request->filled('start_date') || $request->filled('end_date'))
                        <span class="badge badge-info bn-digit font-mono">
                            {{ $request->get('start_date', 'Start') }} &rarr; {{$request->get('end_date', 'End') }}
                        </span>
                    @endif

                    @if($request->filled('order_source') &&$request->get('order_source') !== 'all')
                        <span class="badge badge-info">
                            Channel: {{ strtoupper($request->get('order_source')) }}
                        </span>
                    @endif

                    @if($request->filled('payment_status') &&$request->get('payment_status') !== 'all')
                        <span class="badge {{ $request->get('payment_status') === 'paid' ? 'badge-success' : 'badge-danger' }}">
                            Payment: {{ strtoupper($request->get('payment_status')) }}
                        </span>
                    @endif

                    @if($request->filled('status') &&$request->get('status') !== 'all')
                        <span class="badge badge-warn">
                            Status: {{ strtoupper(str_replace('_', ' ', $request->get('status'))) }}
                        </span>
                    @endif

                    @if($request->filled('search'))
                        <span class="badge badge-info">
                            Search: "{{ $request->get('search') }}"
                        </span>
                    @endif
                </div>

                <!-- Orders Data Table -->
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">Order #</th>
                            <th style="width: 105px;">Invoice</th>
                            <th style="width: 95px;">Date &amp; Time</th>
                            <th style="width: 220px;">Customer Info</th>
                            <th class="text-center" style="width: 50px;">Channel</th>
                            <th style="width: 95px;">Courier</th>
                            <th style="width: 80px;">Payment</th>
                            <th class="text-center" style="width: 70px;">Status</th>
                            <th class="text-end" style="width: 75px;">Amount</th>
                            <th class="text-end" style="width: 75px;">Paid</th>
                            <th class="text-end" style="width: 65px;">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exportOrders as $order)
                            @php
                                $isPos = strtolower($order->order_source ?? ($order->order_type ?? '')) === 'pos';
                                $pStatus = strtolower($order->payment_status ?? 'unpaid');
                                $isPaid = ($pStatus === 'paid');
                                
                                $orderPaid =$isPaid 
                                    ? (!empty($order->paid_amount) &&$order->paid_amount > 0 ? $order->paid_amount : ($order->grand_total ?? 0)) 
                                    : ($order->paid_amount ?? 0);
                                $orderDue  = max(0, ($order->grand_total ?? 0) - $orderPaid);

                                $status = strtolower($order->order_status ?? 'pending');

                                $customerName    =$order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer'));
                                $customerPhone   =$order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? 'N/A'));
                                $customerAddress =$order->shipping_address ?? ($order->customer_address ?? ($order->address ?? ''));

                                $courierName  =$order->courier_name ?? 'Not Assigned';
                                $trackingCode =$order->courier_tracking_id ?? ($order->parcel_id ?? ($order->tracking_code ?? ''));
                            @endphp
                            <tr>
                                <td class="text-center font-mono bn-digit" style="font-weight: 700; color: #0284c7;">
                                    #{{ $order->id }}
                                </td>
                                <td class="font-mono text-muted bn-digit" style="word-break: break-all;">
                                    {{ $order->invoice_no ?? $order->id }}
                                </td>
                                <td style="font-size: 8.5px;" class="bn-digit">
                                    {{ date('d M Y, h:i A', strtotime($order->created_at)) }}
                                </td>
                                <td>
                                    <strong style="color: #0f172a; display: block; font-size: 9.5px;">
                                        {{ $customerName }}
                                    </strong>
                                    <span style="color: #475569; font-size: 8.5px;" class="font-mono bn-digit">{{ $customerPhone }}</span>
                                    @if(!empty($customerAddress))
                                        <span style="display: block; color: #64748b; font-size: 8px;">
                                            {{ $customerAddress }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $isPos ? 'badge-success' : 'badge-info' }}">
                                        {{ $isPos ? 'POS' : 'WEB' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $courierName }}</div>
                                    @if(!empty($trackingCode))
                                        <small class="font-mono text-muted bn-digit" style="font-size: 8px; display: block; word-break: break-all;">
                                            {{ $trackingCode }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div style="text-transform: uppercase; font-weight: 600; font-size: 8.5px;">
                                        {{ $order->payment_method ?? 'COD' }}
                                    </div>
                                    <span class="badge {{ $isPaid ? 'badge-success' : 'badge-danger' }}" style="font-size: 8px; padding: 1px 4px;">
                                        {{ $isPaid ? 'PAID' : 'UNPAID' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $status === 'delivered' || $status === 'completed' ? 'badge-success' : ($status === 'cancelled' ? 'badge-danger' : 'badge-warn') }}" style="font-size: 8px;">
                                        {{ str_replace('_', ' ', $status) }}
                                    </span>
                                </td>
                                <td class="text-end font-mono bn-digit" style="font-weight: 700; color: #0f172a;">
                                    ৳{{ number_format($order->grand_total ?? 0, 2) }}
                                </td>
                                <td class="text-end font-mono bn-digit" style="color: #15803d; font-weight: 600;">
                                    ৳{{ number_format($orderPaid, 2) }}
                                </td>
                                <td class="text-end font-mono bn-digit" style="color: #b91c1c; font-weight: 600;">
                                    ৳{{ number_format($orderDue, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center" style="padding: 24px; color: #94a3b8;">
                                    No orders found matching the filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <!-- Bottom Summary Totals Row -->
                    <tfoot>
                        <tr>
                            <td colspan="8" style="text-align: right; text-transform: uppercase; letter-spacing: 0.3px; color: #334155; font-size: 9.5px;">
                                <i class="fas fa-calculator" style="color: #0284c7; margin-right: 3px;"></i> Total Summary ({{ $exportOrders->count() }} Orders):
                            </td>
                            <td class="text-end font-mono bn-digit" style="font-size: 10px; font-weight: 800; color: #0f172a;">
                                ৳{{ number_format($totalAmountSum ?? 0, 2) }}
                            </td>
                            <td class="text-end font-mono bn-digit" style="font-size: 10px; font-weight: 800; color: #15803d;">
                                ৳{{ number_format($totalPaidSum ?? 0, 2) }}
                            </td>
                            <td class="text-end font-mono bn-digit" style="font-size: 10px; font-weight: 800; color: #b91c1c;">
                                ৳{{ number_format($totalDueSum ?? 0, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Footer -->
                <div style="margin-top: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 8.5px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 5px;">
                    <span>{{ config('app.name') }} &bull; Official Orders Report Export</span>
                    <span>End of Report</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Horizontal Quick Scroll Dock in Unfit/Actual Mode for Mobile -->
    <div id="mobileScrollDock" class="mobile-scroll-dock">
        <button type="button" class="scroll-nav-btn" onclick="scrollReport('start')" title="Scroll to Order #">
            <i class="fas fa-angles-left"></i> Start
        </button>
        <button type="button" class="scroll-nav-btn" onclick="scrollReport('left')" title="Scroll Left">
            <i class="fas fa-chevron-left"></i> Left
        </button>
        <button type="button" class="scroll-nav-btn" onclick="scrollReport('right')" title="Scroll Right">
            Right <i class="fas fa-chevron-right"></i>
        </button>
        <button type="button" class="scroll-nav-btn" onclick="scrollReport('end')" title="Scroll to Due">
            End <i class="fas fa-angles-right"></i>
        </button>
    </div>

    <!-- Notice Modal -->
    <div class="custom-modal-backdrop" id="reportNoticeModalBackdrop">
        <div class="custom-modal-content">
            <h6 id="reportNoticeModalTitle" style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #0f172a;">Notice</h6>
            <p id="reportNoticeModalMsg" style="margin: 0 0 14px 0; font-size: 12px; color: #475569; line-height: 1.4;"></p>
            <button type="button" class="btn btn-primary btn-compact" style="margin: 0 auto; min-width: 90px;" onclick="closeReportNoticeModal()">OK</button>
        </div>
    </div>

    <script>
        function showReportNotice(message, title = 'Notice') {
            document.getElementById('reportNoticeModalTitle').innerText = title;
            document.getElementById('reportNoticeModalMsg').innerText = message;
            document.getElementById('reportNoticeModalBackdrop').style.display = 'flex';
        }

        function closeReportNoticeModal() {
            document.getElementById('reportNoticeModalBackdrop').style.display = 'none';
        }

        let isFitMode = false;

        // Smart Screen Initialization and Resize Handler
        function updateReportLayout() {
            const isDesktop = window.innerWidth >= 992;
            const stage     = document.getElementById('reportStage');
            const wrapper   = document.getElementById('reportScaleWrapper');
            const dock      = document.getElementById('mobileScrollDock');
            const toggleBtn = document.getElementById('btnToggleZoom');
            const btnText   = document.getElementById('zoomToggleText');

            if (!stage || !wrapper) return;

            if (isDesktop) {
                // Desktop: Perfectly centered 1:1 canvas, zero transform, dock hidden
                wrapper.style.transform = 'none';
                wrapper.style.transformOrigin = 'top center';
                stage.style.justifyContent = 'center';
                stage.style.overflowX = 'visible';
                stage.style.height = 'auto';
                if (dock) dock.style.display = 'none';
                if (toggleBtn) toggleBtn.style.display = 'none';
                isFitMode = false;
            } else {
                // Mobile: Show toggle button
                if (toggleBtn) toggleBtn.style.display = 'inline-flex';

                if (isFitMode) {
                    // Fit Mode: Scale to screen width
                    const availableWidth = window.innerWidth - 16;
                    const scaleRatio = availableWidth / 1080;
                    wrapper.style.transform = `scale(${scaleRatio})`;
                    wrapper.style.transformOrigin = 'top left';
                    stage.style.justifyContent = 'flex-start';
                    stage.style.overflowX = 'hidden';

                    const content = document.getElementById('printable-order-report');
                    const scaledHeight = content.offsetHeight * scaleRatio;
                    stage.style.height = (scaledHeight + 30) + 'px';

                    if (dock) dock.style.display = 'none';
                    if (btnText) btnText.innerText = 'Actual Size';
                } else {
                    // Actual Size Mode: 100% width, starts at column 1 (Order #), smooth scroll dock active
                    wrapper.style.transform = 'none';
                    wrapper.style.transformOrigin = 'top left';
                    stage.style.justifyContent = 'flex-start';
                    stage.style.overflowX = 'auto';
                    stage.style.height = 'auto';

                    if (dock) dock.style.display = 'flex';
                    if (btnText) btnText.innerText = 'Fit Screen';
                }
            }
        }

        // Toggle Between Fit Screen Mode & Actual Scrollable Mode on Mobile
        function toggleFitScreen() {
            isFitMode = !isFitMode;
            updateReportLayout();
            if (!isFitMode) {
                const stage = document.getElementById('reportStage');
                if (stage) stage.scrollLeft = 0;
            }
        }

        // Quick Scroll Execution for Unfit Mode
        function scrollReport(direction) {
            const stage = document.getElementById('reportStage');
            if (!stage) return;
            const step = 280;

            if (direction === 'left') {
                stage.scrollBy({ left: -step, behavior: 'smooth' });
            } else if (direction === 'right') {
                stage.scrollBy({ left: step, behavior: 'smooth' });
            } else if (direction === 'start') {
                stage.scrollTo({ left: 0, behavior: 'smooth' });
            } else if (direction === 'end') {
                stage.scrollTo({ left: stage.scrollWidth, behavior: 'smooth' });
            }
        }

        window.addEventListener('resize', updateReportLayout);
        window.addEventListener('DOMContentLoaded', () => {
            updateReportLayout();
            const stage = document.getElementById('reportStage');
            if (stage) stage.scrollLeft = 0;
        });

        // Direct 1-Click A4 Landscape PDF Download
        function downloadPdfDirectly() {
            const btn = document.getElementById('btn-download-pdf');
            const btnText = document.getElementById('btn-pdf-text');
            const wrapper = document.getElementById('reportScaleWrapper');
            const originalText = btnText.innerHTML;

            const prevTransform = wrapper.style.transform;
            wrapper.style.transform = 'none';

            btn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating PDF...';

            const element = document.getElementById('printable-order-report');

            const opt = {
                margin:       [4, 4, 4, 4],
                filename:     'Orders_Report_{{ date("Y_m_d_His") }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.disabled = false;
                btnText.innerHTML = originalText;
                wrapper.style.transform = prevTransform;
            }).catch(err => {
                console.error('PDF generation error:', err);
                btn.disabled = false;
                btnText.innerHTML = originalText;
                wrapper.style.transform = prevTransform;
                showReportNotice('Failed to generate PDF automatically. You can click "Print" and select "Save as PDF".', 'PDF Notice');
            });
        }
    </script>
</body>
</html>