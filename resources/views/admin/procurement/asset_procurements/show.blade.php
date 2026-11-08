
@extends('layouts.admin')

@section('title', 'Asset #' . $asset->asset_code . ' — ' . $asset->name)

@section('admin_content')
<div class="container-fluid px-3 py-3 asset-show-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 asset-page-header">
        <div class="asset-heading">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h5 class="fw-bold text-dark mb-0 asset-page-title">
                    <i class="fas fa-boxes-packing text-primary me-2"></i>Fixed Asset: {{ $asset->name }}
                </h5>

                <span class="badge bg-light text-dark border px-2 py-2 font-monospace asset-code-badge">
                    Tag: {{ $asset->asset_code }}
                </span>

                {{-- Status Badge --}}
                @switch($asset->status ?? 'active')
                    @case('active')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-2 asset-status-badge">
                            <i class="fas fa-circle-check me-1"></i> In-Service / Active
                        </span>
                        @break
                    @case('under_maintenance')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-2 asset-status-badge">
                            <i class="fas fa-screwdriver-wrench me-1"></i> Under Maintenance
                        </span>
                        @break
                    @case('disposed')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-2 asset-status-badge">
                            <i class="fas fa-ban me-1"></i> Disposed / Decommissioned
                        </span>
                        @break
                    @case('written_off')
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-2 asset-status-badge">
                            <i class="fas fa-file-excel me-1"></i> Fully Written Off
                        </span>
                        @break
                    @default
                        <span class="badge bg-light text-dark border px-2 py-2 asset-status-badge">
                            {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                        </span>
                @endswitch
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 asset-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Procurement</li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $asset->asset_code }}</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap gap-2 asset-header-actions">
            @php
                $indexUrl = Route::has('admin.procurement-assets.index')
                    ? route('admin.procurement-assets.index')
                    : (Route::has('admin.asset-procurements.index') ? route('admin.asset-procurements.index') : url('admin/procurement/procurement-assets'));
            @endphp

            <a href="{{ $indexUrl }}" class="btn btn-sm btn-outline-secondary asset-action-btn">
                <i class="fas fa-arrow-left me-1"></i> Back to Register
            </a>

            <button type="button" class="btn btn-sm btn-outline-primary asset-action-btn" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Asset Card
            </button>

            @if(!in_array($asset->status ?? 'active', ['disposed', 'written_off']))
                <button type="button" class="btn btn-sm btn-outline-danger asset-action-btn" data-bs-toggle="modal" data-bs-target="#disposeAssetModal">
                    <i class="fas fa-hand-holding-dollar me-1"></i> Dispose / Write Off
                </button>
            @endif
        </div>
    </div>

    {{-- Financial Valuation & Depreciation Status Tiles --}}
    @php
        $purchaseCost = (float) $asset->purchase_cost;
        $installation = (float) ($asset->installation_cost ?? 0);
        $shipping = (float) ($asset->shipping_cost ?? 0);
        $capitalizedCost = (float) ($asset->capitalized_cost ?? ($purchaseCost + $installation + $shipping));
        $salvageVal = (float) ($asset->salvage_value ?? 0);
        $depreciableBase = max(0, $capitalizedCost - $salvageVal);

        $accumulatedDepr = (float) ($asset->accumulated_depreciation ?? 0);
        $netBookValue = max($salvageVal, $capitalizedCost - $accumulatedDepr);
        $depreciatedPct = $depreciableBase > 0 ? min(100, round(($accumulatedDepr / $depreciableBase) * 100)) : 0;
    @endphp

    <div class="row g-3 mb-4 asset-summary-row">
        <div class="col-6 col-md-3">
            <div class="card asset-summary-card asset-summary-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold asset-summary-label">Capitalized Cost</div>
                            <h4 class="fw-bold text-dark mb-0 mt-2 asset-summary-value">৳{{ number_format($capitalizedCost, 2) }}</h4>
                        </div>
                        <div class="asset-summary-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card asset-summary-card asset-summary-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold asset-summary-label">Accumulated Depreciation</div>
                            <h4 class="fw-bold text-danger mb-0 mt-2 asset-summary-value">৳{{ number_format($accumulatedDepr, 2) }}</h4>
                        </div>
                        <div class="asset-summary-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-arrow-trend-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card asset-summary-card asset-summary-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold asset-summary-label">Written-Down Value (WDV)</div>
                            <h4 class="fw-bold text-success mb-0 mt-2 asset-summary-value">৳{{ number_format($netBookValue, 2) }}</h4>
                        </div>
                        <div class="asset-summary-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card asset-summary-card asset-summary-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="min-w-0">
                            <div class="text-muted small fw-semibold asset-summary-label">Residual / Scrap Value</div>
                            <h4 class="fw-bold text-secondary mb-0 mt-2 asset-summary-value">৳{{ number_format($salvageVal, 2) }}</h4>
                        </div>
                        <div class="asset-summary-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-recycle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Sections --}}
    <div class="row g-3 mb-4 asset-details-row">

        {{-- Asset Specifications & Identification --}}
        <div class="col-12 col-md-4">
            <div class="card asset-info-card h-100">
                <div class="card-header asset-section-header">
                    <span class="fw-bold text-secondary text-uppercase">
                        <i class="fas fa-barcode text-primary me-2"></i>Technical Specifications
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Category:</span>
                        <span class="fw-bold text-dark text-end asset-info-value">{{ $asset->category->name ?? 'Uncategorized' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Brand / Make:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->brand ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Model Number:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->model_no ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Serial / Chassis #:</span>
                        <span class="font-monospace text-dark text-end asset-info-value">{{ $asset->serial_number ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Warranty Expiry:</span>
                        <span class="text-end asset-info-value">
                            @if($asset->warranty_expiry_date)
                                <span class="{{ \Carbon\Carbon::parse($asset->warranty_expiry_date)->isPast() ? 'text-danger' : 'text-success' }} fw-semibold">
                                    {{ \Carbon\Carbon::parse($asset->warranty_expiry_date)->format('d M, Y') }}
                                </span>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-3 small py-2 asset-info-row">
                        <span class="text-muted asset-info-label">Attachment / Scan:</span>
                        <span class="text-end asset-info-value">
                            @if($asset->attachment_file)
                                <a href="{{ asset('storage/' . $asset->attachment_file) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 asset-inline-btn">
                                    <i class="fas fa-file-pdf me-1"></i> View Scan
                                </a>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sourcing, Placement & Physical Custody --}}
        <div class="col-12 col-md-4">
            <div class="card asset-info-card h-100">
                <div class="card-header asset-section-header">
                    <span class="fw-bold text-secondary text-uppercase">
                        <i class="fas fa-location-dot text-primary me-2"></i>Custody &amp; Procurement Sourcing
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Vendor / Supplier:</span>
                        <span class="fw-bold text-dark text-end asset-info-value">
                            @if($asset->supplier && Route::has('admin.suppliers.ledger'))
                                <a href="{{ route('admin.suppliers.ledger', $asset->supplier_id) }}" class="text-primary text-decoration-none">
                                    {{ $asset->supplier->name }}
                                </a>
                            @else
                                <span class="text-muted">{{ $asset->supplier->name ?? 'Direct Capitalization' }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Linked Purchase Bill:</span>
                        <span class="text-end asset-info-value">
                            @if($asset->purchase_id && Route::has('admin.purchases.show'))
                                <a href="{{ route('admin.purchases.show', $asset->purchase_id) }}" class="fw-semibold text-primary text-decoration-none">
                                    #{{ $asset->purchase->purchase_number ?? $asset->purchase_id }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Assigned Branch:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->branch->name ?? 'HQ Branch' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Department:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->department->name ?? 'Corporate' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Current Custodian:</span>
                        <span class="fw-bold text-secondary text-end asset-info-value">{{ $asset->custodian->name ?? 'Unassigned Custodian' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 asset-info-row">
                        <span class="text-muted asset-info-label">Physical Location:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->location ?? 'Not Specified' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Depreciation Policy & General Ledger Mapping --}}
        <div class="col-12 col-md-4">
            <div class="card asset-info-card h-100">
                <div class="card-header asset-section-header">
                    <span class="fw-bold text-secondary text-uppercase">
                        <i class="fas fa-chart-pie text-primary me-2"></i>Depreciation &amp; GL Mapping
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Depreciation Method:</span>
                        <span class="fw-bold text-dark text-end asset-info-value">{{ ucwords(str_replace('_', ' ', $asset->depreciation_method ?? 'Straight Line')) }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Useful Life / Rate:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->useful_life_years ?? 5 }} Years ({{ $asset->depreciation_rate ?? 20 }}%/yr)</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">In-Service Date:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ \Carbon\Carbon::parse($asset->in_service_date ?? $asset->purchase_date)->format('d M, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Fixed Asset Head:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->assetAccount->name ?? 'Plant & Machinery' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 small py-2 border-bottom asset-info-row">
                        <span class="text-muted asset-info-label">Accumulated Depr Head:</span>
                        <span class="fw-semibold text-dark text-end asset-info-value">{{ $asset->accumulatedDepreciationAccount->name ?? 'Acc. Depreciation Contra-Asset' }}</span>
                    </div>

                    {{-- Amortization Progress --}}
                    <div class="pt-3">
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted fw-semibold">Amortized:</span>
                            <span class="fw-bold text-dark">{{ $depreciatedPct }}%</span>
                        </div>
                        <div class="progress asset-depreciation-progress">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $depreciatedPct }}%;" aria-valuenow="{{ $depreciatedPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Depreciation Amortization Schedule Table --}}
    <div class="card asset-data-card mb-4">
        <div class="card-header asset-table-card-header">
            <div>
                <h6 class="fw-bold mb-1 text-dark asset-card-title">
                    <i class="fas fa-calendar-days text-primary me-2"></i>Depreciation Amortization Schedule &amp; Book Value Progression
                </h6>
                <p class="text-muted small mb-0">Annual depreciation and closing book value overview.</p>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-2 asset-method-badge">
                Method: {{ ucwords(str_replace('_', ' ', $asset->depreciation_method ?? 'straight_line')) }}
            </span>
        </div>

        <div class="table-responsive asset-table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 asset-data-table">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 50px;">Year</th>
                        <th style="width: 140px;">Financial Period</th>
                        <th class="text-end" style="width: 150px;">Opening WDV (৳)</th>
                        <th class="text-end" style="width: 150px;">Depreciation Expense (৳)</th>
                        <th class="text-end" style="width: 160px;">Accumulated Depr (৳)</th>
                        <th class="text-end pe-3" style="width: 160px;">Closing WDV / Book Value (৳)</th>
                        <th class="text-center" style="width: 110px;">Posting Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lifeYears = max(1, (int) ($asset->useful_life_years ?? 5));
                        $annualRate = (float) ($asset->depreciation_rate ?? ($lifeYears > 0 ? (100 / $lifeYears) : 20));
                        $isSLM = ($asset->depreciation_method ?? 'straight_line') === 'straight_line';
                        $annualSLMCharge = $lifeYears > 0 ? ($depreciableBase / $lifeYears) : 0;

                        $currentOpening = $capitalizedCost;
                        $runningAcc = 0;
                        $inServiceYear = \Carbon\Carbon::parse($asset->in_service_date ?? $asset->purchase_date)->year;
                    @endphp

                    @for($i = 1; $i <= $lifeYears; $i++)
                        @php
                            $periodYear = $inServiceYear + $i - 1;
                            if ($isSLM) {
                                $deprCharge = min($currentOpening - $salvageVal, $annualSLMCharge);
                            } else {
                                $deprCharge = min($currentOpening - $salvageVal, $currentOpening * ($annualRate / 100));
                            }
                            $deprCharge = max(0, $deprCharge);
                            $runningAcc += $deprCharge;
                            $closingWDV = max($salvageVal, $currentOpening - $deprCharge);
                            $isPosted = ($runningAcc <= $accumulatedDepr);
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $i }}</td>
                            <td class="fw-semibold text-dark">FY {{ $periodYear }} &ndash; {{ $periodYear + 1 }}</td>
                            <td class="text-end">৳{{ number_format($currentOpening, 2) }}</td>
                            <td class="text-end fw-semibold text-danger">৳{{ number_format($deprCharge, 2) }}</td>
                            <td class="text-end text-muted">৳{{ number_format($runningAcc, 2) }}</td>
                            <td class="text-end pe-3 fw-bold text-dark">৳{{ number_format($closingWDV, 2) }}</td>
                            <td class="text-center">
                                @if($isPosted)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 asset-posting-badge">
                                        <i class="fas fa-check me-1"></i> Posted
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 asset-posting-badge">
                                        Projected
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @php
                            $currentOpening = $closingWDV;
                        @endphp
                    @endfor
                </tbody>
            </table>
        </div>

        @if(!empty($asset->notes))
            <div class="card-body p-3 bg-light border-top">
                <h6 class="fw-bold text-secondary small text-uppercase mb-2">Asset Operational Remarks</h6>
                <div class="p-3 bg-white border rounded small text-secondary asset-notes">
                    {{ $asset->notes }}
                </div>
            </div>
        @endif
    </div>

    {{-- Automated Capitalization Journal Entry Representation --}}
    <div class="card asset-data-card mb-4">
        <div class="card-header asset-table-card-header">
            <div>
                <h6 class="fw-bold mb-1 text-dark asset-card-title">
                    <i class="fas fa-receipt text-primary me-2"></i>Initial Capitalization Ledger Journal Entries
                </h6>
                <p class="text-muted small mb-0">Accounting representation of the asset's initial capitalization.</p>
            </div>
        </div>

        <div class="table-responsive asset-table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0 asset-data-table asset-journal-table">
                <thead class="table-light text-secondary text-uppercase">
                    <tr>
                        <th>Account Head</th>
                        <th>Classification</th>
                        <th>Ledger Impact</th>
                        <th class="text-end" style="width: 160px;">Debit (৳)</th>
                        <th class="text-end" style="width: 160px;">Credit (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold text-dark">
                            {{ $asset->assetAccount->name ?? 'Fixed Asset Account' }} ({{ $asset->name }})
                        </td>
                        <td class="text-muted">Non-Current Asset</td>
                        <td class="text-success small">Balance Sheet Asset Addition</td>
                        <td class="text-end fw-bold text-success">৳{{ number_format($capitalizedCost, 2) }}</td>
                        <td class="text-end text-muted">—</td>
                    </tr>
                    @if(!empty($asset->purchase_id))
                        <tr>
                            <td class="fw-semibold text-dark">
                                Accounts Payable / Clearing &mdash; {{ $asset->supplier->name ?? 'Vendor' }}
                            </td>
                            <td class="text-muted">Current Liability / Clearing</td>
                            <td class="text-muted small">Procurement Liability</td>
                            <td class="text-end text-muted">—</td>
                            <td class="text-end fw-bold text-danger">৳{{ number_format($capitalizedCost, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="fw-semibold text-dark">Cash / Bank / Capital Surplus Account</td>
                            <td class="text-muted">Asset Outflow / Equity</td>
                            <td class="text-muted small">Direct Disbursement Capitalization</td>
                            <td class="text-end text-muted">—</td>
                            <td class="text-end fw-bold text-danger">৳{{ number_format($capitalizedCost, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end text-uppercase">Balanced Capitalization:</td>
                        <td class="text-end text-dark">৳{{ number_format($capitalizedCost, 2) }}</td>
                        <td class="text-end text-dark">৳{{ number_format($capitalizedCost, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Dispose / Write-Off Asset Modal --}}
@if(!in_array($asset->status ?? 'active', ['disposed', 'written_off']))
    <div class="modal fade text-start asset-dispose-modal" id="disposeAssetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="fas fa-hand-holding-dollar me-2"></i>Dispose / Write Off Asset #{{ $asset->asset_code }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                @php
                    $disposeUrl = Route::has('admin.procurement-assets.dispose')
                        ? route('admin.procurement-assets.dispose', $asset->id)
                        : url('admin/procurement/procurement-assets/' . $asset->id . '/dispose');
                @endphp

                <form action="{{ $disposeUrl }}" method="POST">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="p-3 bg-light border rounded small text-secondary mb-3">
                            <div class="text-muted mb-1">Current Net Book Value (WDV)</div>
                            <strong class="text-dark fs-5">৳{{ number_format($netBookValue, 2) }}</strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Disposal Type <span class="text-danger">*</span></label>
                            <select name="disposal_type" class="form-select form-select-sm" required>
                                <option value="sale">Scrap Sale / Commercial Disposal</option>
                                <option value="write_off">Accidental Loss / Full Write-Off</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Sale / Realized Scrap Proceeds (৳)</label>
                            <input type="number" step="0.01" name="disposal_amount" class="form-control form-control-sm text-end" placeholder="0.00" value="0.00" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Disposal Date <span class="text-danger">*</span></label>
                            <input type="date" name="disposal_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Decommission Reason <span class="text-danger">*</span></label>
                            <textarea name="disposal_notes" rows="3" class="form-control form-control-sm" required placeholder="State reason for decommissioning or sale particulars..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-check me-1"></i> Confirm Disposal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Kaiadmin White Classic UI Styles --}}
<style>
    .asset-show-page {
        --asset-border: #e6e9ef;
        --asset-muted: #6b7280;
        --asset-heading: #1f2937;
        --asset-surface: #ffffff;
        --asset-page-bg: #f8f9fa;
        --asset-radius: 8px;
        color: var(--asset-heading);
        min-width: 0;
    }

    .asset-show-page .asset-page-header {
        min-width: 0;
    }

    .asset-show-page .asset-page-title {
        font-size: 1.1rem;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .asset-show-page .asset-code-badge,
    .asset-show-page .asset-status-badge {
        font-size: 11px;
        font-weight: 600;
        border-radius: 5px;
        white-space: normal;
    }

    .asset-show-page .asset-breadcrumb {
        font-size: 12px;
    }

    .asset-show-page .asset-breadcrumb a {
        color: var(--bs-primary);
        text-decoration: none;
    }

    .asset-show-page .asset-breadcrumb a:hover {
        text-decoration: underline;
    }

    .asset-show-page .asset-header-actions {
        align-items: center;
    }

    .asset-show-page .asset-action-btn {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 7px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: normal;
    }

    .asset-show-page .card {
        background: var(--asset-surface);
        border: 1px solid var(--asset-border) !important;
        border-radius: var(--asset-radius) !important;
        box-shadow: 0 2px 8px rgba(31, 41, 55, 0.045) !important;
        overflow: hidden;
    }

    .asset-show-page .asset-summary-card {
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .asset-show-page .asset-summary-card:hover {
        box-shadow: 0 5px 16px rgba(31, 41, 55, 0.08) !important;
        transform: translateY(-2px);
    }

    .asset-show-page .asset-summary-card .card-body {
        padding: 18px;
    }

    .asset-show-page .asset-summary-label {
        font-size: 12px;
        line-height: 1.5;
    }

    .asset-show-page .asset-summary-value {
        font-size: clamp(15px, 1.45vw, 21px);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .asset-show-page .asset-summary-icon {
        width: 43px;
        height: 43px;
        min-width: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 18px;
    }

    .asset-show-page .asset-section-header,
    .asset-show-page .asset-table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        background: #fff;
        border-bottom: 1px solid var(--asset-border);
    }

    .asset-show-page .asset-section-header {
        min-height: 49px;
        font-size: 11px;
        letter-spacing: .45px;
    }

    .asset-show-page .asset-section-header span {
        line-height: 1.5;
    }

    .asset-show-page .asset-info-row {
        min-width: 0;
        line-height: 1.55;
    }

    .asset-show-page .asset-info-label {
        flex: 0 0 42%;
        min-width: 0;
    }

    .asset-show-page .asset-info-value {
        flex: 1 1 auto;
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .asset-show-page .asset-inline-btn {
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .asset-show-page .asset-depreciation-progress {
        height: 7px;
        overflow: hidden;
        border-radius: 10px;
        background: #edf0f4;
    }

    .asset-show-page .asset-depreciation-progress .progress-bar {
        border-radius: 10px;
    }

    .asset-show-page .asset-data-card {
        min-width: 0;
    }

    .asset-show-page .asset-table-card-header {
        flex-wrap: wrap;
        padding: 16px 18px;
    }

    .asset-show-page .asset-card-title {
        font-size: 13px;
        line-height: 1.5;
    }

    .asset-show-page .asset-method-badge {
        font-size: 11px;
        font-weight: 600;
        border-radius: 5px;
    }

    .asset-show-page .asset-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .asset-show-page .asset-data-table {
        width: 100%;
        min-width: 900px;
        margin-bottom: 0;
        font-size: 12px;
    }

    .asset-show-page .asset-data-table th {
        padding: 12px 10px;
        background: #f8f9fb;
        color: #667085;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        line-height: 1.5;
        white-space: normal;
        vertical-align: middle;
    }

    .asset-show-page .asset-data-table td {
        padding: 11px 10px;
        vertical-align: middle;
        line-height: 1.5;
    }

    .asset-show-page .asset-data-table tbody tr:hover {
        background-color: #f9fafc;
    }

    .asset-show-page .asset-posting-badge {
        font-size: 10px;
        font-weight: 600;
        border-radius: 5px;
        white-space: nowrap;
    }

    .asset-show-page .asset-journal-table {
        min-width: 760px;
    }

    .asset-show-page .asset-journal-table th {
        font-size: 10px;
    }

    .asset-show-page .asset-journal-table tfoot td {
        padding: 12px 10px;
        background: #f8f9fb;
    }

    .asset-show-page .asset-notes {
        border-radius: 6px !important;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .asset-dispose-modal .modal-content {
        border-radius: 9px !important;
        overflow: hidden;
    }

    .asset-dispose-modal .modal-header,
    .asset-dispose-modal .modal-footer {
        padding: 14px 18px;
        background: #f8f9fb;
        border-color: var(--asset-border);
    }

    .asset-dispose-modal .modal-body {
        padding: 18px !important;
    }

    .asset-dispose-modal .form-label {
        color: #475467;
        font-size: 12px;
        margin-bottom: 6px;
    }

    .asset-dispose-modal .form-control,
    .asset-dispose-modal .form-select {
        min-height: 36px;
        border-color: #dfe3e8;
        border-radius: 6px;
        font-size: 13px;
    }

    .asset-dispose-modal textarea.form-control {
        min-height: 76px;
    }

    .asset-dispose-modal .form-control:focus,
    .asset-dispose-modal .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 .15rem rgba(var(--bs-primary-rgb), .12);
    }

    .asset-dispose-modal .btn {
        min-height: 34px;
        border-radius: 6px;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .asset-show-page .asset-summary-card .card-body {
            padding: 15px;
        }

        .asset-show-page .asset-info-label {
            flex-basis: 40%;
        }
    }

    @media (max-width: 767.98px) {
        .asset-show-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .asset-show-page .asset-page-header {
            align-items: flex-start !important;
        }

        .asset-show-page .asset-heading {
            width: 100%;
        }

        .asset-show-page .asset-header-actions {
            width: 100%;
        }

        .asset-show-page .asset-action-btn {
            flex: 1 1 auto;
        }

        .asset-show-page .asset-summary-card .card-body {
            padding: 13px;
        }

        .asset-show-page .asset-summary-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 15px;
        }

        .asset-show-page .asset-summary-label {
            font-size: 11px;
        }

        .asset-show-page .asset-summary-value {
            font-size: 15px;
        }

        .asset-show-page .asset-section-header {
            padding: 13px 14px;
        }

        .asset-show-page .asset-table-card-header {
            align-items: flex-start;
            padding: 14px;
        }

        .asset-show-page .asset-card-title {
            font-size: 12px;
        }

        .asset-show-page .asset-method-badge {
            white-space: normal;
        }
    }

    @media (max-width: 575.98px) {
        .asset-show-page .asset-page-title {
            font-size: 15px;
        }

        .asset-show-page .asset-code-badge,
        .asset-show-page .asset-status-badge {
            font-size: 10px;
        }

        .asset-show-page .asset-header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
        }

        .asset-show-page .asset-header-actions .asset-action-btn {
            width: 100%;
            min-height: 38px;
            padding: 7px 8px;
            font-size: 11px;
        }

        .asset-show-page .asset-header-actions .asset-action-btn:last-child:nth-child(3) {
            grid-column: 1 / -1;
        }

        .asset-show-page .asset-summary-card .card-body {
            padding: 11px;
        }

        .asset-show-page .asset-summary-icon {
            width: 31px;
            height: 31px;
            min-width: 31px;
            font-size: 13px;
        }

        .asset-show-page .asset-summary-value {
            font-size: 13px;
        }

        .asset-show-page .asset-info-row {
            align-items: flex-start !important;
            gap: 8px !important;
        }

        .asset-show-page .asset-info-label {
            flex-basis: 43%;
            font-size: 11px;
        }

        .asset-show-page .asset-info-value {
            font-size: 11px;
        }

        .asset-show-page .asset-table-card-header {
            gap: 10px;
        }

        .asset-show-page .asset-method-badge {
            font-size: 10px;
        }

        .asset-dispose-modal .modal-dialog {
            margin: 12px;
        }
    }

    @media print {
        @page {
            size: auto;
            margin: 12mm;
        }

        body {
            background: #fff !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .admin-dark-sidebar,
        .admin-navbar,
        .breadcrumb,
        .btn,
        .dropdown,
        .modal,
        .card-header a {
            display: none !important;
        }

        .asset-show-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .asset-show-page .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            break-inside: avoid;
        }

        .asset-show-page .asset-summary-card {
            break-inside: avoid;
        }

        .asset-show-page .table-responsive {
            overflow: visible !important;
        }

        .asset-show-page .asset-data-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        .asset-show-page .asset-data-table th,
        .asset-show-page .asset-data-table td {
            padding: 5px !important;
            border: 1px solid #dee2e6 !important;
        }

        .asset-show-page .asset-info-row {
            break-inside: avoid;
        }

        .asset-show-page .asset-summary-card:hover {
            transform: none;
        }
    }
</style>
@endsection
