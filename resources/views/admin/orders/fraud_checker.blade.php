@extends('layouts.admin')

@section('title', 'Fraud Checker - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #fraudCheckerRoot .btn,
    #fraudCheckerRoot .btn-sm,
    #fraudCheckerRoot button,
    #fraudCheckerRoot .badge,
    #fraudCheckerRoot .form-control,
    #fraudCheckerRoot .input-group-text,
    #fraudCheckerRoot .alert,
    #fraudCheckerRoot .progress,
    #fraudCheckerRoot .progress-bar,
    #fraudCheckerRoot .fraud-stat-box,
    #fraudCheckerRoot .proof-media-surface,
    #fraudCheckerRoot .proof-thumb-box,
    .fraud-modal-scope .modal-content,
    .fraud-modal-scope .btn,
    .fraud-modal-scope .form-control {
        border-radius: 5px !important;
    }

    #fraudCheckerRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #fraudCheckerRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact Button Sizing */
    #fraudCheckerRoot .btn-compact,
    .fraud-modal-scope .btn-compact {
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

    /* Enterprise Surface Cards */
    #fraudCheckerRoot .fraud-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 18px;
        border-radius: 8px !important;
    }

    /* Safe Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Form Controls */
    #fraudCheckerRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #fraudCheckerRoot .form-control {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #fraudCheckerRoot .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Modern Loading State Overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(2px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 20;
        border-radius: 8px !important;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease-in-out;
    }
    .loading-active .loading-overlay {
        opacity: 1;
        visibility: visible;
    }

    /* Global Media Chooser Surface for Proof Attachment */
    .proof-media-surface {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        padding: 12px;
        transition: border-color 0.15s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
    }
    .proof-thumb-box {
        width: 44px;
        height: 44px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Table Styling */
    .custom-courier-table {
        margin-bottom: 0;
        width: 100%;
    }
    .custom-courier-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 10px;
        white-space: nowrap;
    }
    .custom-courier-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* 100/100 Mobile Responsiveness Tuning */
    @media (max-width: 767.98px) {
        .fraud-header h5 {
            font-size: 16px !important;
        }
        .header-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-action-btns .btn-compact {
            width: 100%;
        }
        #fraudCheckerRoot .fraud-card {
            padding: 12px !important;
        }
        .fraud-stat-box {
            padding: 8px 6px !important;
        }
        .fraud-stat-box h5 {
            font-size: 16px !important;
        }
        .custom-courier-table th,
        .custom-courier-table td {
            padding: 7px 8px !important;
            font-size: 11.5px;
        }
        .proof-actions-wrap {
            flex-direction: column;
            align-items: stretch !important;
        }
        .proof-actions-wrap .btn-compact {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="fraudCheckerRoot">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 fraud-header">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-shield-alt text-primary"></i>
                    <span>Fraud Checker</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 10px;">
                    Courier Risk Radar
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Nationwide real-time courier performance, delivery success ratio, and parcel return risk verification.
            </small>
        </div>
        <div class="header-action-btns d-flex gap-2">
            <a href="{{ route('admin.system.setting.fraud') }}" class="btn btn-outline-dark btn-compact shadow-xs">
                <i class="fas fa-sliders me-1"></i>
                <span>API Settings</span>
            </a>
            <a href="{{ route('admin.order.online') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Orders</span>
            </a>
        </div>
    </div>

    <!-- Phone Search Card -->
    <div class="fraud-card mb-3">
        <form id="fraudCheckForm" onsubmit="return false;">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <label class="form-label text-center d-block mb-2" for="phoneInput">
                        Enter Customer Phone Number to Verify
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fas fa-phone" style="font-size: 11px;"></i>
                        </span>
                        <input type="text" id="phoneInput" class="form-control font-monospace bn-digit border-start-0" placeholder="e.g. 018XXXXXXXX" value="{{ $searchedPhone ?? '' }}" required autofocus>
                        <button type="button" id="analyzeBtn" class="btn ms-2 btn-primary btn-compact px-3 px-md-4 shadow-xs">
                            <span id="btnText"><i class="fas fa-magnifying-glass me-1"></i> Analyze Number</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                    <small class="text-muted d-block text-center mt-1.5" style="font-size: 10.5px;">
                        Supports all Bangladesh operators.
                    </small>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="row g-3 mb-3">
        <!-- 1. All-Bangladesh Courier Performance (External API) -->
        <div class="col-12 col-lg-7">
            <div class="fraud-card h-100 position-relative" id="externalCard">
                <div class="loading-overlay">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <span class="small fw-bold text-dark" style="font-size: 11.5px;">Checking Nationwide Couriers...</span>
                </div>

                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                        <i class="fas fa-truck text-primary"></i>
                        <span>All-Bangladesh Courier Performance</span>
                    </h6>
                    <span class="badge bg-primary text-white font-monospace px-2 py-0.5" style="font-size: 9.5px;">
                        FraudBD LIVE
                    </span>
                </div>

                <!-- 4 Key Stat Metric Boxes -->
                <div class="row text-center g-2 mb-3">
                    <div class="col-6 col-sm-3">
                        <div class="p-2 bg-light border fraud-stat-box">
                            <span class="text-muted d-block" style="font-size: 10px;">Total Parcels</span>
                            <h5 class="fw-bold mb-0 text-dark font-monospace bn-digit mt-1" id="extTotal">0</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2 bg-success-subtle border border-success-subtle fraud-stat-box">
                            <span class="text-success d-block" style="font-size: 10px;">Delivered</span>
                            <h5 class="fw-bold mb-0 text-success font-monospace bn-digit mt-1" id="extSuccess">0</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2 bg-danger-subtle border border-danger-subtle fraud-stat-box">
                            <span class="text-danger d-block" style="font-size: 10px;">Cancelled</span>
                            <h5 class="fw-bold mb-0 text-danger font-monospace bn-digit mt-1" id="extCancel">0</h5>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2 bg-primary-subtle border border-primary-subtle fraud-stat-box">
                            <span class="text-primary d-block" style="font-size: 10px;">Success Rate</span>
                            <h5 class="fw-bold mb-0 text-primary font-monospace bn-digit mt-1" id="extRate">0%</h5>
                        </div>
                    </div>
                </div>

                <!-- Delivery Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small fw-semibold mb-1">
                        <span class="text-muted" style="font-size: 11px;">Delivery Completion Ratio</span>
                        <span class="fw-bold text-dark font-monospace bn-digit" id="extRateLabel" style="font-size: 11px;">0%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" id="extProgressBar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Rating / Risk Remarks Box -->
                <div id="extRemarksBox" class="alert alert-success-subtle border border-success-subtle p-2 small mb-3 text-success fw-bold d-none" style="font-size: 11px;">
                    <i class="fas fa-check-circle me-1"></i> <span id="extRemarksText"></span>
                </div>

                <!-- Courier-wise Breakdown Table -->
                <div id="courierTableWrapper" class="mb-3">
                    <h6 class="fw-bold text-dark small text-uppercase mb-2" style="font-size: 11px;">
                        <i class="fas fa-list-check text-primary me-1"></i> Courier Partner Breakdown:
                    </h6>
                    <div class="table-responsive border" style="-webkit-overflow-scrolling: touch;">
                        <table class="table custom-courier-table align-middle text-nowrap">
                            <thead>
                                <tr>
                                    <th>Courier</th>
                                    <th class="text-center">Status / Rating</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Delivered</th>
                                    <th class="text-center">Cancelled</th>
                                </tr>
                            </thead>
                            <tbody id="courierTableBody">
                                <tr>
                                    <td class="fw-bold text-dark"><i class="fas fa-truck-fast text-secondary me-1"></i> Pathao</td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">Standard</span></td>
                                    <td class="text-center font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-success font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-danger font-monospace bn-digit fw-bold">0</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark"><i class="fas fa-truck-fast text-secondary me-1"></i> Steadfast</td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">Standard</span></td>
                                    <td class="text-center font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-success font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-danger font-monospace bn-digit fw-bold">0</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark"><i class="fas fa-truck-fast text-secondary me-1"></i> Paperfly</td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">Standard</span></td>
                                    <td class="text-center font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-success font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-danger font-monospace bn-digit fw-bold">0</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark"><i class="fas fa-truck-fast text-secondary me-1"></i> Redx</td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">Standard</span></td>
                                    <td class="text-center font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-success font-monospace bn-digit fw-bold">0</td>
                                    <td class="text-center text-danger font-monospace bn-digit fw-bold">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="apiErrorBox" class="alert alert-warning border border-warning-subtle small p-2.5 my-2 d-none" style="font-size: 11.5px;"></div>
            </div>
        </div>

        <!-- 2. Internal Store Order History -->
        <div class="col-12 col-lg-5">
            <div class="fraud-card h-100 position-relative" id="internalCard">
                <div class="loading-overlay">
                    <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
                    <span class="small fw-bold text-dark" style="font-size: 11.5px;">Searching Store Orders...</span>
                </div>

                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                        <i class="fas fa-store text-success"></i>
                        <span>Our Store History</span>
                    </h6>
                    <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 9.5px;">
                        INTERNAL
                    </span>
                </div>

                <div id="internalStats" class="d-none">
                    <div class="row text-center g-2 mb-3">
                        <div class="col-4">
                            <div class="p-2 bg-light border fraud-stat-box">
                                <small class="text-muted d-block" style="font-size: 10px;">Total Placed</small>
                                <h6 class="fw-bold mb-0 text-dark font-monospace bn-digit mt-1" id="intTotal">0</h6>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-success-subtle border border-success-subtle fraud-stat-box">
                                <small class="text-success d-block" style="font-size: 10px;">Delivered</small>
                                <h6 class="fw-bold mb-0 text-success font-monospace bn-digit mt-1" id="intDelivered">0</h6>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-danger-subtle border border-danger-subtle fraud-stat-box">
                                <small class="text-danger d-block" style="font-size: 10px;">Cancelled</small>
                                <h6 class="fw-bold mb-0 text-danger font-monospace bn-digit mt-1" id="intCancelled">0</h6>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark small text-uppercase mb-2" style="font-size: 11px;">
                        <i class="fas fa-clock-rotate-left text-secondary me-1"></i> Previous Order Records:
                    </h6>
                    <div class="table-responsive border mb-3" style="max-height: 220px; overflow-y: auto;">
                        <ul class="list-group list-group-flush small" id="internalOrderList"></ul>
                    </div>
                </div>

                <div id="internalEmpty" class="text-center py-5 text-muted small my-auto">
                    <i class="fas fa-user-clock fa-2x mb-2 d-block opacity-40"></i>
                    <span id="internalEmptyText">Enter a customer phone number to analyze.</span>
                </div>

                <!-- Integrated Global Media Chooser for Suspect Evidence / Fraud Proof -->
                <div class="border-top pt-3 mt-auto">
                    <label class="form-label mb-1">
                        <i class="fas fa-paperclip text-primary me-1"></i> Suspect Evidence / Fraud Proof
                    </label>
                    <div class="proof-media-surface">
                        <div class="proof-actions-wrap d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseFraudProofMedia()">
                                <i class="fas fa-photo-film me-1"></i>
                                <span>Attach from Media Library</span>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-compact shadow-xs d-none" id="btnClearFraudProof" onclick="clearFraudProofMedia()">
                                <i class="fas fa-trash-alt me-1"></i>
                                <span>Clear Proof</span>
                            </button>
                        </div>

                        <div id="proofPreviewWrap" class="d-none align-items-center justify-content-between gap-2 bg-white border p-2" style="border-radius: 5px;">
                            <div class="d-flex align-items-center gap-2 min-width-0">
                                <div class="proof-thumb-box" onclick="zoomFraudProof()" title="Click to Zoom Evidence">
                                    <img id="fraudProofThumbImg" src="https://placehold.co/100x100?text=Proof" alt="Fraud Evidence" onerror="this.src='https://placehold.co/100x100?text=Proof';">
                                </div>
                                <div class="text-truncate">
                                    <span class="small fw-bold text-success d-block text-truncate" style="font-size: 11px;">
                                        <i class="fas fa-check-circle me-1"></i>Evidence Attached
                                    </span>
                                    <small class="text-muted d-block text-truncate font-monospace" id="fraudProofPathText" style="font-size: 9.5px; max-width: 180px;"></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-compact py-1 px-2" style="font-size: 10px !important;" onclick="zoomFraudProof()" title="Zoom">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>

                        <div id="noFraudProofNotice" class="text-center py-1 text-muted" style="font-size: 11px;">
                            <i class="fas fa-image me-1 opacity-50"></i> Optional: Attach fake delivery slip, chat screenshot, or suspect proof.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Evidence Zoom Lightbox Modal -->
<div class="modal fade fraud-modal-scope" id="proofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i> Fraud Evidence Screenshot
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalZoomImgTag" src="" alt="Evidence" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 4px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Bootstrap Notice Dialog Modal (Replaces browser alert) -->
<div class="modal fade fraud-modal-scope" id="fraudNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="fraudNoticeIconWrap" style="width: 44px; height: 44px;">
                    <i class="fas fa-circle-info fs-4" id="fraudNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="fraudNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="fraudNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const analyzeBtn = document.getElementById('analyzeBtn');
    const phoneInput = document.getElementById('phoneInput');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const extCard = document.getElementById('externalCard');
    const intCard = document.getElementById('internalCard');

    function showNoticeModal(message, title = 'Notification', type = 'info') {
        const titleEl  = document.getElementById('fraudNoticeTitle');
        const msgEl    = document.getElementById('fraudNoticeMsg');
        const iconWrap = document.getElementById('fraudNoticeIconWrap');
        const iconEl   = document.getElementById('fraudNoticeIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconWrap && iconEl) {
            if (type === 'error' || type === 'danger') {
                iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-exclamation fs-4';
            } else if (type === 'success') {
                iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-check fs-4';
            } else {
                iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-info fs-4';
            }
        }

        const modalEl = document.getElementById('fraudNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function performFraudCheck() {
        const phone = phoneInput.value.trim();
        if (!phone) {
            phoneInput.focus();
            showNoticeModal('Please enter a valid customer phone number to analyze.', 'Phone Required', 'error');
            return;
        }

        analyzeBtn.disabled = true;
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        extCard.classList.add('loading-active');
        intCard.classList.add('loading-active');

        const requestUrl = "{{ route('admin.order.fraud') }}?phone=" + encodeURIComponent(phone);

        fetch(requestUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            renderExternal(data.externalReport, data.apiError);
            renderInternal(data.internalReport, phone);
        })
        .catch(err => {
            console.error(err);
            const errBox = document.getElementById('apiErrorBox');
            errBox.textContent = 'Connection error. Please try again.';
            errBox.classList.remove('d-none');
        })
        .finally(() => {
            analyzeBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            extCard.classList.remove('loading-active');
            intCard.classList.remove('loading-active');
        });
    }

    analyzeBtn.addEventListener('click', performFraudCheck);

    phoneInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performFraudCheck();
        }
    });

    function renderExternal(report, apiError) {
        const errBox = document.getElementById('apiErrorBox');
        const remarksBox = document.getElementById('extRemarksBox');

        if (apiError) {
            errBox.textContent = apiError;
            errBox.classList.remove('d-none');
            remarksBox.classList.add('d-none');
            return;
        }
        errBox.classList.add('d-none');

        if (report && report.totalSummary) {
            const sum = report.totalSummary;
            document.getElementById('extTotal').textContent = sum.total || 0;
            document.getElementById('extSuccess').textContent = sum.success || 0;
            document.getElementById('extCancel').textContent = sum.cancel || 0;

            const rate = parseFloat(sum.successRate || 0);
            document.getElementById('extRate').textContent = rate + '%';
            document.getElementById('extRateLabel').textContent = rate + '%';

            const bar = document.getElementById('extProgressBar');
            bar.style.width = rate + '%';
            bar.className = 'progress-bar ' + (rate < 50 ? 'bg-danger' : (rate < 75 ? 'bg-warning text-dark' : 'bg-success'));

            if (report.remarks) {
                document.getElementById('extRemarksText').textContent = 'Risk Rating: ' + report.remarks;
                remarksBox.classList.remove('d-none');
            } else {
                remarksBox.classList.add('d-none');
            }

            if (report.summaries && Object.keys(report.summaries).length > 0) {
                const tbody = document.getElementById('courierTableBody');
                tbody.innerHTML = '';
                for (const [cName, cData] of Object.entries(report.summaries)) {
                    const row = `
                        <tr>
                            <td class="fw-bold text-dark">
                                ${cData.logo ? `<img src="${cData.logo}" alt="${cName}" style="height: 16px; margin-right: 6px; vertical-align: middle;">` : '<i class="fas fa-truck-fast text-secondary me-1"></i> '}
                                ${cName}
                            </td>
                            <td class="text-center">
                                ${cData.data_type === 'rating' ? 
                                    `<span class="badge bg-info-subtle text-info border px-2 py-0.5">${(cData.customer_rating || 'Rating').replace('_', ' ')}</span>` : 
                                    `<span class="badge bg-light text-muted border">Standard</span>`}
                            </td>
                            <td class="text-center fw-bold font-monospace bn-digit">${cData.total || 0}</td>
                            <td class="text-center text-success fw-bold font-monospace bn-digit">${cData.success || 0}</td>
                            <td class="text-center text-danger fw-bold font-monospace bn-digit">${cData.cancel || 0}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                }
            }
        }
    }

    function renderInternal(report, phone) {
        const stats = document.getElementById('internalStats');
        const empty = document.getElementById('internalEmpty');
        const emptyText = document.getElementById('internalEmptyText');
        const list = document.getElementById('internalOrderList');

        if (report && report.total_orders > 0) {
            document.getElementById('intTotal').textContent = report.total_orders;
            document.getElementById('intDelivered').textContent = report.delivered;
            document.getElementById('intCancelled').textContent = report.cancelled;

            list.innerHTML = '';
            report.history.forEach(ord => {
                const li = `
                    <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-1.5 border-bottom">
                        <a href="/admin/orders/view/${ord.id}" class="fw-bold font-monospace bn-digit text-primary text-decoration-none">
                            #${ord.invoice_no || ord.id}
                        </a>
                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 9.5px;">${(ord.order_status || '').replace('_', ' ')}</span>
                        <span class="font-monospace bn-digit fw-bold text-dark">৳ ${parseFloat(ord.grand_total || 0).toFixed(2)}</span>
                    </li>
                `;
                list.insertAdjacentHTML('beforeend', li);
            });

            stats.classList.remove('d-none');
            empty.classList.add('d-none');
        } else {
            stats.classList.add('d-none');
            empty.classList.remove('d-none');
            emptyText.innerHTML = `No previous store orders found for <strong>${phone}</strong>.`;
        }
    }

    // Auto-trigger analysis if searchedPhone was passed from server
    if (phoneInput.value.trim().length >= 10) {
        performFraudCheck();
    }
});

// Global Media Chooser Integration for Suspect Evidence Attachment
function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;
    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
    if (!item || typeof item !== 'object') return null;
    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl  = item.url || item.file_path || item.path || '';
    if (!rawPath || rawPath === 'undefined') return null;
    return {
        path: String(rawPath).trim(),
        url:  String(rawUrl).trim()
    };
}

function chooseFraudProofMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    const thumbImg    = document.getElementById('fraudProofThumbImg');
                    const pathText    = document.getElementById('fraudProofPathText');
                    const previewWrap = document.getElementById('proofPreviewWrap');
                    const emptyNotice = document.getElementById('noFraudProofNotice');
                    const btnClear    = document.getElementById('btnClearFraudProof');

                    if (thumbImg) thumbImg.src = picked.url;
                    if (pathText) pathText.innerText = picked.path;
                    if (previewWrap) {
                        previewWrap.classList.remove('d-none');
                        previewWrap.classList.add('d-flex');
                    }
                    if (emptyNotice) emptyNotice.classList.add('d-none');
                    if (btnClear) btnClear.classList.remove('d-none');

                    const modalEl = document.getElementById('fraudNoticeModal');
                    if (modalEl && window.bootstrap) {
                        document.getElementById('fraudNoticeTitle').innerText = 'Evidence Attached';
                        document.getElementById('fraudNoticeMsg').innerText = 'Fraud evidence screenshot attached from Global Media Library.';
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                }
            }
        });
    } else {
        const modalEl = document.getElementById('fraudNoticeModal');
        if (modalEl && window.bootstrap) {
            document.getElementById('fraudNoticeTitle').innerText = 'Notice';
            document.getElementById('fraudNoticeMsg').innerText = 'Global Media Chooser is loading or not available on this layout.';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
}

function clearFraudProofMedia() {
    const previewWrap = document.getElementById('proofPreviewWrap');
    const emptyNotice = document.getElementById('noFraudProofNotice');
    const btnClear    = document.getElementById('btnClearFraudProof');

    if (previewWrap) {
        previewWrap.classList.add('d-none');
        previewWrap.classList.remove('d-flex');
    }
    if (emptyNotice) emptyNotice.classList.remove('d-none');
    if (btnClear) btnClear.classList.add('d-none');
}

function zoomFraudProof() {
    const thumbImg   = document.getElementById('fraudProofThumbImg');
    const zoomImgTag = document.getElementById('modalZoomImgTag');
    if (thumbImg && zoomImgTag && thumbImg.src) {
        zoomImgTag.src = thumbImg.src;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('proofZoomModal')).show();
    }
}
</script>
@endsection