@php
    $resolveBrandLogo = function($path) {
        $fallback = 'https://placehold.co/50x50?text=Brand';
        if (empty($path)) {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };
@endphp

@forelse($brands as $b)
    @php
        $img = $resolveBrandLogo($b->logo ?? null);
    @endphp
    <tr id="brand-row-{{ $b->id }}" class="align-middle">
        <!-- ID -->
        <td class="text-center text-nowrap py-2" style="width: 50px;">
            <span class="text-secondary small fw-bold font-monospace bn-digit" style="font-size: 11.5px;">#{{ $b->id }}</span>
        </td>

        <!-- Thumbnail (Logo) -->
        <td class="text-center py-2" style="width: 70px;">
            <img src="{{ $img }}" onerror="this.onerror=null; this.src='https://placehold.co/50x50?text=Brand';" class="border object-fit-cover shadow-xs bg-white p-1" style="width: 42px; height: 42px; border-radius: 5px !important;" alt="brand">
        </td>

        <!-- Brand Name & Description -->
        <td class="py-2" style="min-width: 180px;">
            <div>
                <strong class="text-dark d-block" style="font-size: 13px; line-height: 1.35;">{{ $b->name }}</strong>
                @if(!empty($b->description))
                    <small class="text-muted d-block text-wrap mt-1" style="max-width: 320px; font-size: 11px; line-height: 1.35;">{{ $b->description }}</small>
                @endif
            </div>
        </td>

        <!-- Slug -->
        <td class="font-monospace small text-muted text-nowrap py-2" style="font-size: 11.5px;">
            {{ $b->slug }}
        </td>

        <!-- Product Count -->
        <td class="text-nowrap py-2">
            <span class="badge bg-light text-dark border px-2 py-1 fw-bold small font-monospace bn-digit" style="font-size: 10.5px; border-radius: 5px !important;">
                {{ $b->products_count ?? 0 }} products
            </span>
        </td>

        <!-- Status Toggle -->
        <td class="text-center text-nowrap py-2">
            <div class="form-check form-switch d-inline-block m-0">
                <input class="form-check-input m-0" type="checkbox" role="switch" id="switch-brand-{{ $b->id }}" {{ $b->is_active ? 'checked' : '' }} onchange="toggleBrandStatusAjax({{ $b->id }}, this)" style="cursor: pointer;">
            </div>
        </td>

        <!-- Actions (Strict 5px Radius, 26x26 Square) -->
        <td class="text-end pe-3 text-nowrap py-2">
            <div class="d-inline-flex align-items-center justify-content-end gap-1">
                <button type="button" class="btn btn-sm btn-light border text-primary brand-action-sq-btn btn-edit-brand shadow-xs" data-id="{{ $b->id }}" title="Edit Brand">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light border text-danger brand-action-sq-btn btn-delete-brand shadow-xs" data-id="{{ $b->id }}" data-name="{{ $b->name }}" title="Delete Brand">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted small">
            <i class="fas fa-tags fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No brands found matching criteria.</span>
        </td>
    </tr>
@endforelse