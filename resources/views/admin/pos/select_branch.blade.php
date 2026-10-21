@extends('layouts.admin')

@section('title', 'Select Branch - POS Terminal')

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across Controls */
    #posBranchChooserRoot .btn,
    #posBranchChooserRoot .badge,
    #posBranchChooserRoot .form-control,
    #posBranchChooserRoot .input-group-text,
    #posBranchChooserRoot .card,
    #posBranchChooserRoot .branch-select-card,
    #posBranchChooserRoot .branch-icon-box {
        border-radius: 5px !important;
    }

    /* Safe Typography & Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Enterprise SaaS Card Surface */
    .branch-select-card {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
    }

    .branch-select-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #f59e0b;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .branch-select-card:hover {
        transform: translateY(-3px);
        border-color: #cbd5e1 !important;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08) !important;
    }

    .branch-select-card:hover::before {
        opacity: 1;
    }

    .branch-select-card:active {
        transform: scale(0.99);
    }

    /* Branch Store Icon Box */
    .branch-icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fef3c7;
        color: #d97706;
        font-size: 20px;
        margin: 0 auto;
        transition: all 0.2s ease;
    }

    .branch-select-card:hover .branch-icon-box {
        background: #f59e0b;
        color: #ffffff;
    }

    /* Launch Terminal Action Button */
    .btn-launch-terminal {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        padding: 7px 12px !important;
        line-height: 1.3 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #f8fafc;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .branch-select-card:hover .btn-launch-terminal {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    /* Live Search Input */
    .branch-search-input {
        border: 1px solid #cbd5e1;
        font-size: 12.5px;
        padding: 8px 12px;
        height: 38px;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .branch-search-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Mobile Responsive Layout Tuning */
    @media (max-width: 767.98px) {
        .branch-select-card {
            padding: 14px 12px !important;
        }
        .branch-icon-box {
            width: 42px;
            height: 42px;
            font-size: 18px;
        }
        .branch-title {
            font-size: 14px !important;
        }
        .branch-code-badge {
            font-size: 10px !important;
        }
        .btn-launch-terminal {
            font-size: 11px !important;
            padding: 7px 10px !important;
        }
    }
</style>

<div class="container-fluid py-3 py-md-4 px-2 px-md-4" id="posBranchChooserRoot">
    <!-- Header Section -->
    <div class="row justify-content-center mb-3">
        <div class="col-12 col-md-8 col-lg-6 text-center">
            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1 mb-2" style="font-size: 10px;">
                <i class="fas fa-store me-1"></i> POS TERMINAL LAUNCHPAD
            </span>
            <h4 class="fw-bold text-dark mb-1" style="font-size: 20px;">Select POS Branch</h4>
            <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.4;">
                Choose an active branch outlet below to initiate sales billing, barcode scanning, and order processing.
            </p>

            <!-- Instant Live Branch Search Bar -->
            <div class="input-group mb-2">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="fas fa-search" style="font-size: 11px;"></i>
                </span>
                <input type="text" id="branchSearchInput" class="form-control branch-search-input border-start-0" placeholder="Type branch name or outlet code to search..." autocomplete="off">
                <button type="button" class="btn btn-outline-secondary d-none" id="btnClearBranchSearch" onclick="clearBranchFilter()" style="border-color: #cbd5e1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center px-1">
                <small class="text-muted" style="font-size: 10.5px;">
                    Total Active Branches: <strong class="bn-digit font-monospace text-dark">{{ count($branches) }}</strong>
                </small>
                <small class="text-success fw-semibold" style="font-size: 10.5px;">
                    <i class="fas fa-circle-check me-1"></i> Ready for Terminal
                </small>
            </div>
        </div>
    </div>

    <!-- Branch Cards Grid -->
    <div class="row justify-content-center g-2 g-md-3" id="branchGridContainer">
        @forelse($branches as $b)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 branch-card-wrapper" data-name="{{ strtolower($b->name) }}" data-code="{{ strtolower($b->code) }}">
                <a href="{{ route('admin.pos.terminal', $b->id) }}" class="text-decoration-none h-100 d-block">
                    <div class="card p-3 p-md-4 text-center h-100 branch-select-card shadow-xs">
                        <div>
                            <!-- Branch Icon -->
                            <div class="branch-icon-box mb-2">
                                <i class="fas fa-shop"></i>
                            </div>

                            <!-- Branch Name -->
                            <h5 class="fw-bold text-dark mb-1 branch-title text-truncate" style="font-size: 15px;" title="{{ $b->name }}">
                                {{ $b->name }}
                            </h5>

                            <!-- Branch Code Badge -->
                            <div class="mb-3">
                                <span class="badge bg-light text-secondary border font-monospace bn-digit branch-code-badge px-2 py-0.5" style="font-size: 10.5px;">
                                    <i class="fas fa-hashtag me-0.5 opacity-70"></i> {{ $b->code }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Button Indicator -->
                        <div class="btn-launch-terminal w-100">
                            <span>Launch Terminal</span>
                            <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 col-md-6 text-center py-5">
                <div class="p-4 bg-white border text-center shadow-xs" style="border-radius: 5px;">
                    <i class="fas fa-store-slash fa-3x text-muted mb-3 opacity-40"></i>
                    <h6 class="fw-bold text-dark mb-1">No Active Branch Found</h6>
                    <p class="text-muted small mb-3">Please configure and activate at least one outlet branch in settings first.</p>
                    <a href="{{ route('admin.branch.index') }}" class="btn btn-primary btn-sm px-3 fw-semibold">
                        <i class="fas fa-plus me-1"></i> Add New Branch
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- No Search Results Found Placeholder -->
    <div id="noSearchResultsFound" class="row justify-content-center d-none mt-3">
        <div class="col-12 col-md-6 text-center py-4">
            <div class="p-3 bg-white border text-center shadow-xs" style="border-radius: 5px;">
                <i class="fas fa-magnifying-glass fa-2x text-muted mb-2 opacity-50"></i>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 13px;">No Matching Branch Found</h6>
                <p class="text-muted small mb-2" style="font-size: 11.5px;">No branch matches your search keyword. Check spelling or clear search.</p>
                <button type="button" class="btn btn-outline-dark btn-sm px-3 fw-semibold" onclick="clearBranchFilter()" style="font-size: 11px;">
                    Clear Search
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('branchSearchInput');
    const clearBtn    = document.getElementById('btnClearBranchSearch');
    const cards       = document.querySelectorAll('.branch-card-wrapper');
    const emptyNotice = document.getElementById('noSearchResultsFound');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            let visibleCount = 0;

            if (clearBtn) {
                clearBtn.classList.toggle('d-none', !query);
            }

            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const code = card.getAttribute('data-code') || '';

                if (name.includes(query) || code.includes(query)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (emptyNotice) {
                if (visibleCount === 0 && cards.length > 0) {
                    emptyNotice.classList.remove('d-none');
                } else {
                    emptyNotice.classList.add('d-none');
                }
            }
        });
    }
});

function clearBranchFilter() {
    const searchInput = document.getElementById('branchSearchInput');
    const clearBtn    = document.getElementById('btnClearBranchSearch');
    const cards       = document.querySelectorAll('.branch-card-wrapper');
    const emptyNotice = document.getElementById('noSearchResultsFound');

    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }
    if (clearBtn) {
        clearBtn.classList.add('d-none');
    }
    cards.forEach(card => {
        card.style.display = 'block';
    });
    if (emptyNotice) {
        emptyNotice.classList.add('d-none');
    }
}
</script>
@endsection