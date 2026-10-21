@php
    $resolveCatThumb = function($path) {
        $fallback = 'https://placehold.co/50x50?text=Icon';
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

@forelse($categories as $cat)
    @php
        $img = $resolveCatThumb($cat->image ?? null);
        $isChild = ($cat->type === 'child');
    @endphp
    <tr id="cat-row-{{ $cat->type }}-{{ $cat->id }}" class="align-middle {{ $isChild ? 'bg-light bg-opacity-50' : '' }}">
        <!-- ID -->
        <td class="text-center text-nowrap py-2" style="width: 45px;">
            <span class="text-secondary small fw-bold font-monospace bn-digit" style="font-size: 11.5px;">#{{ $cat->id }}</span>
        </td>

        <!-- Thumb / Icon -->
        <td class="text-center py-2" style="width: 60px;">
            @if(!empty($cat->image))
                <img src="{{ $img }}" onerror="this.onerror=null; this.src='https://placehold.co/50x50?text=Icon';" class="border object-fit-cover shadow-xs" style="width: 40px; height: 40px; border-radius: 5px !important;" alt="thumb">
            @elseif(!empty($cat->icon))
                <div class="border bg-white d-inline-flex align-items-center justify-content-center text-primary shadow-xs" style="width: 40px; height: 40px; font-size: 16px; border-radius: 5px !important;">
                    <i class="{{ $cat->icon }}"></i>
                </div>
            @else
                <div class="border bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px; font-size: 15px; border-radius: 5px !important;">
                    <i class="fas fa-folder"></i>
                </div>
            @endif
        </td>

        <!-- Category Name & Type Badge -->
        <td class="py-2" style="min-width: 200px;">
            <div class="d-flex align-items-center">
                @if($isChild)
                    <span class="text-primary me-2 fw-bold flex-shrink-0" style="font-size: 15px;">↳</span>
                @endif
                <div>
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <strong class="text-dark" style="font-size: 13px; line-height: 1.35;">{{ $cat->name }}</strong>
                        @if($isChild)
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 text-nowrap" style="font-size: 9.5px; border-radius: 5px !important;">Sub-category</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 text-nowrap" style="font-size: 9.5px; border-radius: 5px !important;">Parent Category</span>
                        @endif
                    </div>
                    @if(!empty($cat->description))
                        <small class="text-muted d-block text-wrap mt-1" style="max-width: 260px; font-size: 11px; line-height: 1.35;">{{ $cat->description }}</small>
                    @endif
                </div>
            </div>
        </td>

        <!-- Slug -->
        <td class="font-monospace small text-muted text-nowrap py-2" style="font-size: 11.5px;">
            {{ $cat->slug }}
        </td>

        <!-- Parent Category -->
        <td class="text-nowrap py-2">
            @if(!empty($cat->parent_name))
                <span class="badge bg-info-subtle text-dark border border-info-subtle px-2 py-1 small" style="font-size: 10.5px; border-radius: 5px !important;">
                    <i class="fas fa-folder me-1 text-info"></i>{{ $cat->parent_name }}
                </span>
            @else
                <span class="text-muted small" style="font-size: 11.5px;">— (Root)</span>
            @endif
        </td>

        <!-- Items Count -->
        <td class="text-nowrap py-2">
            <span class="badge bg-light text-dark border px-2 py-1 fw-bold small font-monospace bn-digit" style="font-size: 10.5px; border-radius: 5px !important;">
                {{ $cat->products_count ?? 0 }} items
            </span>
        </td>

        <!-- Status Toggle -->
        <td class="text-center text-nowrap py-2">
            <div class="form-check form-switch d-inline-block m-0">
                <input class="form-check-input m-0" type="checkbox" role="switch" id="switch-status-{{ $cat->type }}-{{ $cat->id }}" {{ $cat->is_active ? 'checked' : '' }} onchange="toggleCatStatusAjax({{ $cat->id }}, '{{ $cat->type }}', this)" style="cursor: pointer;">
            </div>
        </td>

        <!-- Actions (Strict 5px Radius, 26x26 Square) -->
        <td class="text-end pe-3 text-nowrap py-2">
            <div class="d-inline-flex align-items-center justify-content-end gap-1">
                <!-- View Live Storefront -->
                <a href="{{ route('category.products', $cat->slug) }}" target="_blank" class="btn btn-sm btn-light border text-info cat-action-sq-btn shadow-xs" title="View Storefront">
                    <i class="fas fa-eye"></i>
                </a>

                <!-- Edit Button -->
                <button type="button" class="btn btn-sm btn-light border text-primary cat-action-sq-btn btn-edit-category shadow-xs" data-id="{{ $cat->id }}" data-type="{{ $cat->type }}" title="Edit Category">
                    <i class="fas fa-edit"></i>
                </button>

                <!-- Delete Button -->
                <button type="button" class="btn btn-sm btn-light border text-danger cat-action-sq-btn btn-delete-category shadow-xs" data-id="{{ $cat->id }}" data-type="{{ $cat->type }}" data-name="{{ $cat->name }}" title="Delete Category">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted small">
            <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No categories found matching criteria.</span>
        </td>
    </tr>
@endforelse