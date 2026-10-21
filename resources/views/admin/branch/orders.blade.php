@extends('layouts.admin')

@section('title', 'Branch Orders - ' . config('app.name'))

@section('admin_content')
<style>
    /* পেজিনেশন ও এসভিজি আইকন সাইজ লক */
    .card-footer nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px;
        width: 100% !important;
    }
    @media (min-width: 768px) {
        .card-footer nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }
    .card-footer nav svg {
        width: 16px !important;
        height: 16px !important;
        max-width: 16px !important;
        max-height: 16px !important;
        vertical-align: middle;
        display: inline-block !important;
    }
    .card-footer .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 4px;
        padding-left: 0;
        list-style: none;
    }
    .card-footer .page-item .page-link,
    .card-footer .page-item span {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 32px;
        height: 32px;
        padding: 4px 8px;
        font-size: 13px;
        color: #475569;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        text-decoration: none;
    }
    .card-footer .page-item.active .page-link,
    .card-footer .page-item.active span {
        color: #fff !important;
        background-color: #03172e !important;
        border-color: #03172e !important;
        font-weight: 700;
    }
    .card-footer .page-item.disabled .page-link,
    .card-footer .page-item.disabled span {
        color: #94a3b8 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none;
    }

    @media (max-width: 767.98px) {
        .branch-orders-header h5 {
            font-size: 1.15rem;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 branch-orders-header">
        <h5 class="fw-bold text-dark mb-0">Branch Orders ({{ $orders->total() }})</h5>
        <a href="{{ route('admin.pos.index') }}" class="btn btn-warning btn-sm fw-bold text-white px-3 shadow-sm align-self-start align-self-md-auto">
            <i class="fas fa-cash-register me-1"></i> POS Order
        </a>
    </div>

    <!-- ফিল্টারিং বার -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <input type="text" id="orderSearchInput" class="form-control form-control-sm rounded-pill" placeholder="Search by invoice...">
            </div>
            <div class="col-12 col-md-3">
                <select id="orderBranchSelect" class="form-select form-select-sm rounded-pill">
                    <option value="">Select Branch</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- টেবিল কার্ড -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small text-nowrap">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 50px;">SL</th>
                        <th>ORDER / INVOICE</th>
                        <th>BRANCH</th>
                        <th>CUSTOMER</th>
                        <th>TOTAL PRICE</th>
                        <th>PAYMENT</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3">ACTION</th>
                    </tr>
                </thead>
                <tbody id="branchOrdersTableBody">
                    @forelse($orders as $index => $o)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $orders->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.order.view', $o->id) }}" class="fw-bold text-primary font-monospace">
                                    #{{ $o->invoice_no ?? $o->id }}
                                </a>
                                <small class="d-block text-muted">{{ date('d M Y, h:i A', strtotime($o->created_at)) }}</small>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary border">{{ $o->branch_name ?? 'Head Office' }}</span></td>
                            <td style="min-width: 160px;">
                                <span class="d-block text-truncate" style="max-width: 200px;">
                                    {{ $o->customer_name ?? ($o->shipping_name ?? 'Walk-in Customer') }}
                                </span>
                            </td>
                            <td class="fw-bold">{{ number_format($o->grand_total, 2) }} ৳</td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $o->payment_status ?? 'paid' }}</span></td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ', $o->order_status) }}</span></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.order.view', $o->id) }}" class="btn btn-outline-primary btn-sm px-2 py-1 rounded-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No branch orders recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3 overflow-auto">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection