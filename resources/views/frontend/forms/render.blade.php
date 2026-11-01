@php
    // Ensure $errors variable is always available for widgets, popups & shortcodes
    $errors = $errors ?? (session()->has('errors') ? session()->get('errors') : new \Illuminate\Support\ViewErrorBag);

    $formInstanceId = 'dynamic-form-' . $form->id . '-' . uniqid();

    // Universal Helper for resolving form assets or default files
    $resolveFormAsset = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;

        if (class_exists(\App\Services\ImagePathService::class)) {
            $serviceUrl = \App\Services\ImagePathService::resolveUrl($path, $fallback);
            if (!empty($serviceUrl)) return $serviceUrl;
        }

        if (function_exists('show_image')) {
            $showUrl = show_image($path, $fallback);
            if (!empty($showUrl)) return $showUrl;
        }

        $clean = ltrim(str_replace(['public_html/', 'public/'], '', $path), '/');

        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        if (file_exists(public_path('storage/' . $clean))) return asset('storage/' . $clean);

        if (str_starts_with($clean, 'storage/') || str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }

        $cleanWithoutStorage = ltrim(str_replace('storage/', '', $clean), '/');
        if (file_exists(storage_path('app/public/' . $cleanWithoutStorage))) {
            return asset('storage/' . $cleanWithoutStorage);
        }

        return asset($clean);
    };

    // Styling & Appearance Settings Extraction (Safely Parsed for PHP 8.3+)
    $styles = is_array($form->styles) ? $form->styles : (json_decode($form->styles ?? '', true) ?? []);

    // Custom HTML ID and Custom Container CSS Class
    $customId    = !empty($styles['custom_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $styles['custom_id']) : null;
    $customClass = $styles['custom_class'] ?? '';
    $wrapperId   = $customId ?: 'form-wrapper-' . $formInstanceId;

    // Layout, Flow & Label Controls
    $layoutDir    = $styles['layout'] ?? 'vertical';
    $fieldFlow    = $styles['field_flow'] ?? ($layoutDir === 'horizontal' ? 'single_line' : 'inline');
    $showLabels   = isset($styles['show_labels']) ? (bool)$styles['show_labels'] : true;
    $btnAlign     = $styles['button_align'] ?? 'left'; // left, center, right, full, inline_last
    $btnSize      = $styles['button_size'] ?? 'md';
    $btnText      = !empty($styles['button_text']) ? $styles['button_text'] : ($form->submit_button_text ?: 'Submit');

    // Form Container Styling
    $formBg       = $styles['form_bg'] ?? '#ffffff';
    $formBorder   = $styles['border_color'] ?? '#e2e8f0';
    $formRadius   = isset($styles['border_radius']) ? (int)$styles['border_radius'] : 8;
    $formPadding  = isset($styles['form_padding']) ? (int)$styles['form_padding'] : 24;

    // Field & Typography Styling
    $inputBg      = $styles['input_bg'] ?? '#ffffff';
    $inputText    = $styles['input_text'] ?? '#0f172a';
    $inputBorder  = $styles['input_border'] ?? '#cbd5e1';
    $inputRadius  = isset($styles['input_radius']) ? (int)$styles['input_radius'] : 6;
    $labelColor   = $styles['label_color'] ?? '#1e293b';

    // Button Styling
    $btnBg        = $styles['btn_bg'] ?? '#2563eb';
    $btnTextCol   = $styles['btn_text'] ?? '#ffffff';
    $btnHoverBg   = $styles['btn_hover_bg'] ?? '#1d4ed8';
    $btnHoverText = $styles['btn_hover_text'] ?? '#ffffff';
    $btnRadius    = isset($styles['btn_radius']) ? (int)$styles['btn_radius'] : 6;

    $btnSizeStyle = match($btnSize) {
        'sm'    => 'min-height: 36px; padding: 6px 16px; font-size: 12.5px;',
        'lg'    => 'min-height: 52px; padding: 13px 32px; font-size: 16px;',
        default => 'min-height: 45px; padding: 10px 24px; font-size: 14px;',
    };

    // Header Logo Resolver (Checks direct logo column, banner, and fallback styles array)
    $formLogoRaw = $form->logo ?? ($form->banner ?? ($styles['logo'] ?? ($styles['header_logo'] ?? null)));
    $formLogoUrl = !empty($formLogoRaw) ? $resolveFormAsset($formLogoRaw) : null;
@endphp

<div
    class="dynamic-form-wrapper df-flow-{{ $fieldFlow }} {{ $layoutDir === 'horizontal' ? 'form-layout-inline' : '' }} {{ $customClass }}"
    id="{{ $wrapperId }}"
    data-instance-id="{{ $formInstanceId }}"
>

    {{-- =========================================================
        FORM HEADER (LOGO, TITLE & DESCRIPTION)
    ========================================================== --}}
    @if($formLogoUrl || (($form->show_title ?? true) && !empty($form->name)) || (($form->show_description ?? true) && !empty($form->description)))

        <div class="dynamic-form-header">

            @if($formLogoUrl)
                <div class="dynamic-form-logo-box">
                    <img src="{{ $formLogoUrl }}" alt="{{ $form->name }}" class="dynamic-form-logo img-fluid" onerror="this.onerror=null; this.src='{{ asset(ltrim($formLogoRaw, '/')) }}';">
                </div>
            @endif

            @if(($form->show_title ?? true) && !empty($form->name))
                <h2 class="dynamic-form-title theme-section-title">
                    {{ $form->name }}
                </h2>
            @endif

            @if(($form->show_description ?? true) && !empty($form->description))
                <div class="dynamic-form-description">
                    {!! nl2br(e($form->description)) !!}
                </div>
            @endif

        </div>

    @endif


    {{-- =========================================================
        AJAX MESSAGE
    ========================================================== --}}

    <div
        class="dynamic-form-ajax-message"
        id="form-message-{{ $formInstanceId }}"
        role="alert"
        aria-live="polite"
    ></div>


    {{-- =========================================================
        SUCCESS MESSAGE (WITH INSTANT COUPON REVEAL & COPY)
    ========================================================== --}}

    <div
        class="dynamic-form-success"
        id="form-success-{{ $formInstanceId }}"
        style="display:none;"
    >

        <div class="dynamic-form-success-icon">
            ✓
        </div>

        <h3 id="form-success-title-{{ $formInstanceId }}">
            Message Sent Successfully!
        </h3>

        <p
            class="dynamic-form-success-text"
            id="form-success-text-{{ $formInstanceId }}"
        ></p>

        {{-- Instant Coupon Reveal Card (Shown automatically when Coupon form is submitted) --}}
        <div class="dynamic-form-coupon-reveal" id="form-coupon-wrap-{{ $formInstanceId }}" style="display:none;">
            <div class="dynamic-form-coupon-card">
                <span class="dynamic-form-coupon-badge">
                    <i class="fas fa-gift me-1"></i> Special Discount Voucher
                </span>
                <div class="dynamic-form-coupon-code-row">
                    <span class="dynamic-form-coupon-code" id="form-coupon-code-{{ $formInstanceId }}">CODE</span>
                    <button type="button" class="dynamic-form-coupon-copy" id="form-coupon-copy-{{ $formInstanceId }}">
                        <i class="fas fa-copy me-1"></i> Copy Code
                    </button>
                </div>
                <small class="dynamic-form-coupon-hint">Use this promo code at checkout to claim your discount!</small>
            </div>
        </div>

        <button
            type="button"
            class="dynamic-form-success-again"
            id="form-success-again-{{ $formInstanceId }}"
        >
            Send Another Message
        </button>

    </div>


    {{-- =========================================================
        FORM
    ========================================================== --}}

    <form
        id="{{ $formInstanceId }}"
        class="dynamic-form"
        action="{{ route('forms.submit', $form) }}"
        method="POST"
        enctype="multipart/form-data"
        novalidate
    >

        @csrf


        {{-- =====================================================
            FIELDS (GRID SUPPORT WITH 50%+50% INLINE OR STACKED)
        ====================================================== --}}

        <div class="dynamic-form-grid">

            @forelse($form->fields as $field)

                @php
                    $fieldName = trim((string) $field->name);
                    $fieldId = 'form-field-' . $field->id . '-' . uniqid();
                    $columnWidth = (int) ($field->column_width ?: 12);

                    if ($columnWidth < 1) {
                        $columnWidth = 12;
                    }

                    if ($columnWidth > 12) {
                        $columnWidth = 12;
                    }

                    $oldValue = old($fieldName, $field->default_value);
                @endphp


                {{-- =================================================
                    HTML
                ================================================== --}}

                @if($field->type === 'html')

                    <div
                        class="
                            dynamic-form-field
                            dynamic-form-column-{{ $columnWidth }}
                            dynamic-form-html
                        "
                    >

                        {!! $field->default_value !!}

                    </div>

                    @continue

                @endif


                {{-- =================================================
                    HIDDEN
                ================================================== --}}

                @if($field->type === 'hidden')

                    <input
                        type="hidden"
                        name="{{ $fieldName }}"
                        value="{{ $oldValue }}"
                    >

                    @continue

                @endif


                {{-- =================================================
                    NORMAL FIELD
                ================================================== --}}

                <div
                    class="
                        dynamic-form-field
                        dynamic-form-column-{{ $columnWidth }}
                        {{ $field->wrapper_class }}
                    "
                    data-field-name="{{ $fieldName }}"
                >

                    {{-- LABEL (CONTROLLED BY SHOW/HIDE TOGGLE) --}}

                    @if(
                        $showLabels &&
                        in_array(
                            $field->type,
                            [
                                'text',
                                'email',
                                'phone',
                                'number',
                                'textarea',
                                'select',
                                'file',
                                'date',
                                'time',
                                'url'
                            ]
                        )
                    )

                        <label
                            for="{{ $fieldId }}"
                            class="dynamic-form-label"
                        >

                            {{ $field->label }}

                            @if($field->is_required)

                                <span class="dynamic-form-required">
                                    *
                                </span>

                            @endif

                        </label>

                    @endif


                    {{-- =================================================
                        TEXT
                    ================================================== --}}

                    @if($field->type === 'text')

                        <input
                            type="text"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            placeholder="{{ $field->placeholder ?: (!$showLabels ? $field->label : '') }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                            @if($field->min_length !== null)
                                minlength="{{ $field->min_length }}"
                            @endif
                            @if($field->max_length !== null)
                                maxlength="{{ $field->max_length }}"
                            @endif
                        >


                    {{-- =================================================
                        EMAIL
                    ================================================== --}}

                    @elseif($field->type === 'email')

                        <input
                            type="email"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            placeholder="{{ $field->placeholder ?: (!$showLabels ? $field->label : '') }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                            @if($field->min_length !== null)
                                minlength="{{ $field->min_length }}"
                            @endif
                            @if($field->max_length !== null)
                                maxlength="{{ $field->max_length }}"
                            @endif
                        >


                    {{-- =================================================
                        PHONE
                    ================================================== --}}

                    @elseif($field->type === 'phone')

                        <input
                            type="tel"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            placeholder="{{ $field->placeholder ?: (!$showLabels ? $field->label : '') }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                            @if($field->min_length !== null)
                                minlength="{{ $field->min_length }}"
                            @endif
                            @if($field->max_length !== null)
                                maxlength="{{ $field->max_length }}"
                            @endif
                        >


                    {{-- =================================================
                        NUMBER
                    ================================================== --}}

                    @elseif($field->type === 'number')

                        <input
                            type="number"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            placeholder="{{ $field->placeholder ?: (!$showLabels ? $field->label : '') }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                            @if($field->min_value !== null)
                                min="{{ $field->min_value }}"
                            @endif
                            @if($field->max_value !== null)
                                max="{{ $field->max_value }}"
                            @endif
                        >


                    {{-- =================================================
                        TEXTAREA
                    ================================================== --}}

                    @elseif($field->type === 'textarea')

                        <textarea
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            placeholder="{{ $field->placeholder ?: (!$showLabels ? $field->label : '') }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                            @if($field->min_length !== null)
                                minlength="{{ $field->min_length }}"
                            @endif
                            @if($field->max_length !== null)
                                maxlength="{{ $field->max_length }}"
                            @endif
                        >{{ $oldValue }}</textarea>


                    {{-- =================================================
                        SELECT
                    ================================================== --}}

                    @elseif($field->type === 'select')

                        <select
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                        >

                            <option value="">
                                {{ $field->placeholder ?: (!$showLabels ? 'Select ' . $field->label : 'Select an option') }}
                            </option>

                            @foreach(
                                ($field->options ?: [])
                                as $option
                            )

                                @php
                                    $optionLabel =
                                        is_array($option)
                                            ? ($option['label'] ?? '')
                                            : $option;

                                    $optionValue =
                                        is_array($option)
                                            ? ($option['value'] ?? $optionLabel)
                                            : $option;
                                @endphp

                                <option
                                    value="{{ $optionValue }}"
                                    @selected(
                                        (string) $oldValue ===
                                        (string) $optionValue
                                    )
                                >
                                    {{ $optionLabel }}
                                </option>

                            @endforeach

                        </select>


                    {{-- =================================================
                        RADIO
                    ================================================== --}}

                    @elseif($field->type === 'radio')

                        <div class="dynamic-form-options">

                            @foreach(
                                ($field->options ?: [])
                                as $index => $option
                            )

                                @php
                                    $optionLabel =
                                        is_array($option)
                                            ? ($option['label'] ?? '')
                                            : $option;

                                    $optionValue =
                                        is_array($option)
                                            ? ($option['value'] ?? $optionLabel)
                                            : $option;

                                    $radioId =
                                        $fieldId .
                                        '-' .
                                        $index;
                                @endphp

                                <label
                                    for="{{ $radioId }}"
                                    class="dynamic-form-option"
                                >

                                    <input
                                        type="radio"
                                        id="{{ $radioId }}"
                                        name="{{ $fieldName }}"
                                        value="{{ $optionValue }}"
                                        @checked(
                                            (string) $oldValue ===
                                            (string) $optionValue
                                        )
                                        @if($field->is_required)
                                            required
                                        @endif
                                    >

                                    <span>
                                        {{ $optionLabel }}
                                    </span>

                                </label>

                            @endforeach

                        </div>


                    {{-- =================================================
                        CHECKBOX
                    ================================================== --}}

                    @elseif($field->type === 'checkbox')

                        <div class="dynamic-form-options">

                            @foreach(
                                ($field->options ?: [])
                                as $index => $option
                            )

                                @php
                                    $optionLabel =
                                        is_array($option)
                                            ? ($option['label'] ?? '')
                                            : $option;

                                    $optionValue =
                                        is_array($option)
                                            ? ($option['value'] ?? $optionLabel)
                                            : $option;

                                    $checkboxId =
                                        $fieldId .
                                        '-' .
                                        $index;

                                    $selectedValues =
                                        old(
                                            $fieldName,
                                            []
                                        );

                                    if (
                                        !is_array(
                                            $selectedValues
                                        )
                                    ) {
                                        $selectedValues = [];
                                    }
                                @endphp

                                <label
                                    for="{{ $checkboxId }}"
                                    class="dynamic-form-option"
                                >

                                    <input
                                        type="checkbox"
                                        id="{{ $checkboxId }}"
                                        name="{{ $fieldName }}[]"
                                        value="{{ $optionValue }}"
                                        @checked(
                                            in_array(
                                                $optionValue,
                                                $selectedValues
                                            )
                                        )
                                    >

                                    <span>
                                        {{ $optionLabel }}
                                    </span>

                                </label>

                            @endforeach

                        </div>


                    {{-- =================================================
                        FILE
                    ================================================== --}}

                    @elseif($field->type === 'file')

                        <input
                            type="file"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                        >

                        <div class="dynamic-form-file-info">

                            @if($field->file_max_size)

                                Maximum size:
                                {{ number_format($field->file_max_size / 1024, 2) }}
                                MB

                            @else

                                Maximum size: 5 MB

                            @endif

                            @if($field->file_allowed_extensions)

                                <br>

                                Allowed:
                                {{ $field->file_allowed_extensions }}

                            @endif

                        </div>


                    {{-- =================================================
                        DATE
                    ================================================== --}}

                    @elseif($field->type === 'date')

                        <input
                            type="date"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                        >


                    {{-- =================================================
                        TIME
                    ================================================== --}}

                    @elseif($field->type === 'time')

                        <input
                            type="time"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                        >


                    {{-- =================================================
                        URL
                    ================================================== --}}

                    @elseif($field->type === 'url')

                        <input
                            type="url"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            placeholder="{{ $field->placeholder ?: (!$showLabels ? $field->label : '') }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                        >


                    {{-- =================================================
                        FALLBACK
                    ================================================== --}}

                    @else

                        <input
                            type="text"
                            id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            value="{{ $oldValue }}"
                            placeholder="{{ $field->placeholder ?: (!$showLabels ? $field->label : '') }}"
                            class="
                                dynamic-form-control
                                {{ $field->css_class }}
                            "
                            @if($field->is_required)
                                required
                            @endif
                        >

                    @endif


                    {{-- =================================================
                        HELP TEXT
                    ================================================== --}}

                    @if($field->help_text)

                        <div class="dynamic-form-help">
                            {{ $field->help_text }}
                        </div>

                    @endif


                    {{-- =================================================
                        SERVER ERROR (SAFE CHECK PREVENTING UNDEFINED $errors)
                    ================================================== --}}

                    @if(isset($errors) && $errors->has($fieldName))

                        <div class="dynamic-form-field-error">
                            {{ $errors->first($fieldName) }}
                        </div>

                    @endif

                </div>

            @empty

                <div class="dynamic-form-no-fields">

                    No fields have been added
                    to this form yet.

                </div>

            @endforelse


            {{-- =====================================================
                INLINE LAST SUBMIT BUTTON (SAME ROW BESIDE LAST FIELD)
            ====================================================== --}}

            @if(
                $form->fields->where('type', '!=', 'html')->count() > 0 &&
                $btnAlign === 'inline_last'
            )

                <div class="dynamic-form-field dynamic-form-submit-inline">

                    <button
                        type="submit"
                        class="dynamic-form-submit-button"
                        style="{{ $btnSizeStyle }}"
                    >

                        <span class="dynamic-form-submit-text">
                            {{ $btnText }}
                        </span>

                        <span
                            class="dynamic-form-submit-loading"
                            style="display:none;"
                        >
                            Sending...
                        </span>

                    </button>

                </div>

            @endif

        </div>


        {{-- =========================================================
            STANDARD SUBMIT AREA (LEFT, CENTER, RIGHT, FULL WIDTH)
        ========================================================== --}}

        @if(
            $form->fields->where('type', '!=', 'html')->count() > 0 &&
            $btnAlign !== 'inline_last'
        )

            <div class="dynamic-form-submit-area align-{{ $btnAlign }}">

                <button
                    type="submit"
                    class="dynamic-form-submit-button {{ $btnAlign === 'full' ? 'btn-w-100' : '' }}"
                    style="{{ $btnSizeStyle }}"
                >

                    <span class="dynamic-form-submit-text">
                        {{ $btnText }}
                    </span>

                    <span
                        class="dynamic-form-submit-loading"
                        style="display:none;"
                    >
                        Sending...
                    </span>

                </button>

            </div>

        @endif

    </form>

</div>


{{-- =========================================================
    100% STRICTLY SCOPED CSS (ZERO LEAKAGE TO OTHER FORMS)
========================================================== --}}
<style>

#{{ $wrapperId }},
#form-wrapper-{{ $formInstanceId }} {
    width: 100%;
    box-sizing: border-box;
    background-color: {{ $formBg }} !important;
    border: 1px solid {{ $formBorder }} !important;
    border-radius: {{ $formRadius }}px !important;
    padding: {{ $formPadding }}px !important;
    font-family: inherit;
}

#{{ $wrapperId }} .dynamic-form-header,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-header {
    margin-bottom: 25px;
}

#{{ $wrapperId }} .dynamic-form-logo-box,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-logo-box {
    margin-bottom: 12px;
}

#{{ $wrapperId }} .dynamic-form-logo,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-logo {
    max-height: 65px;
    object-fit: contain;
    display: inline-block;
}

#{{ $wrapperId }} .dynamic-form-title,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-title {
    margin: 0 0 8px;
    font-size: 28px;
    line-height: 1.3;
    font-weight: 700;
    color: {{ $labelColor }} !important;
}

#{{ $wrapperId }} .dynamic-form-description,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-description {
    font-size: 15px;
    line-height: 1.7;
    color: #6b7280;
}

/* =========================================================
   1. FLOW: PASAPASI (INLINE GRID)
========================================================= */
#{{ $wrapperId }}.df-flow-inline .dynamic-form-grid,
#form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-grid {
    display: grid !important;
    grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
    gap: 16px 20px !important;
    width: 100% !important;
}

#{{ $wrapperId }}.df-flow-inline .dynamic-form-field,
#form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-field {
    min-width: 0;
}

#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-1,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-1  { grid-column: span 1 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-2,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-2  { grid-column: span 2 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-3,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-3  { grid-column: span 3 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-4,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-4  { grid-column: span 4 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-5,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-5  { grid-column: span 5 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-6,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-6  { grid-column: span 6 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-7,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-7  { grid-column: span 7 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-8,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-8  { grid-column: span 8 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-9,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-9  { grid-column: span 9 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-10, #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-10 { grid-column: span 10 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-11, #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-11 { grid-column: span 11 !important; }
#{{ $wrapperId }}.df-flow-inline .dynamic-form-column-12, #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-12 { grid-column: span 12 !important; }

/* Inline Last Submit Button Styling (Sits in same row beside field) */
#{{ $wrapperId }} .dynamic-form-submit-inline,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-inline {
    grid-column: span auto !important;
    display: flex !important;
    align-items: flex-end !important;
    justify-content: flex-start !important;
}

/* =========================================================
   2. FLOW: UPOR-NICE (STACKED VERTICAL 100% WIDTH)
========================================================= */
#{{ $wrapperId }}.df-flow-stacked .dynamic-form-grid,
#form-wrapper-{{ $formInstanceId }}.df-flow-stacked .dynamic-form-grid {
    display: flex !important;
    flex-direction: column !important;
    gap: 16px !important;
    width: 100% !important;
}

#{{ $wrapperId }}.df-flow-stacked .dynamic-form-field,
#form-wrapper-{{ $formInstanceId }}.df-flow-stacked .dynamic-form-field {
    width: 100% !important;
    flex: 1 1 100% !important;
}

/* =========================================================
   3. FLOW: SINGLE LINE (INLINE ROW)
========================================================= */
#{{ $wrapperId }}.df-flow-single_line .dynamic-form-grid,
#form-wrapper-{{ $formInstanceId }}.df-flow-single_line .dynamic-form-grid {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: flex-end !important;
    gap: 12px !important;
}

#{{ $wrapperId }}.df-flow-single_line .dynamic-form-field,
#form-wrapper-{{ $formInstanceId }}.df-flow-single_line .dynamic-form-field {
    flex: 1 1 200px !important;
}

#{{ $wrapperId }}.df-flow-single_line .dynamic-form-submit-area,
#form-wrapper-{{ $formInstanceId }}.df-flow-single_line .dynamic-form-submit-area {
    margin-top: 0 !important;
    width: auto !important;
}

/* =========================================================
   FIELD & INPUT STYLING
========================================================= */
#{{ $wrapperId }} .dynamic-form-label,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    line-height: 1.4;
    font-weight: 600;
    color: {{ $labelColor }} !important;
}

#{{ $wrapperId }} .dynamic-form-required,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-required {
    color: #dc2626;
    margin-left: 3px;
}

#{{ $wrapperId }} .dynamic-form-control,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-control {
    display: block;
    width: 100%;
    min-height: 45px;
    padding: 10px 13px;
    border: 1px solid {{ $inputBorder }} !important;
    border-radius: {{ $inputRadius }}px !important;
    background: {{ $inputBg }} !important;
    color: {{ $inputText }} !important;
    font-size: 14px;
    line-height: 1.5;
    outline: none;
    box-sizing: border-box;
    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}

#{{ $wrapperId }} textarea.dynamic-form-control,
#form-wrapper-{{ $formInstanceId }} textarea.dynamic-form-control {
    min-height: 120px;
    resize: vertical;
}

#{{ $wrapperId }} .dynamic-form-control:focus,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-control:focus {
    border-color: {{ $btnBg }} !important;
    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, .12) !important;
}

#{{ $wrapperId }} .dynamic-form-control.is-invalid,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-control.is-invalid {
    border-color: #dc2626 !important;
    box-shadow:
        0 0 0 3px
        rgba(220, 38, 38, .08) !important;
}

#{{ $wrapperId }} .dynamic-form-options,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#{{ $wrapperId }} .dynamic-form-option,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-option {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: {{ $labelColor }} !important;
}

#{{ $wrapperId }} .dynamic-form-option input,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-option input {
    margin: 0;
}

#{{ $wrapperId }} .dynamic-form-help,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-help {
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280;
}

#{{ $wrapperId }} .dynamic-form-file-info,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-file-info {
    margin-top: 6px;
    font-size: 12px;
    line-height: 1.6;
    color: #6b7280;
}

#{{ $wrapperId }} .dynamic-form-field-error,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-field-error {
    margin-top: 6px;
    font-size: 13px;
    color: #dc2626;
}

/* =========================================================
   BUTTON ALIGNMENT & DESIGN
========================================================= */
#{{ $wrapperId }} .dynamic-form-submit-area,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-area {
    margin-top: 24px;
    display: flex;
    width: 100%;
}

#{{ $wrapperId }} .dynamic-form-submit-area.align-left,   #form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-area.align-left   { justify-content: flex-start; }
#{{ $wrapperId }} .dynamic-form-submit-area.align-center, #form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-area.align-center { justify-content: center; }
#{{ $wrapperId }} .dynamic-form-submit-area.align-right,  #form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-area.align-right  { justify-content: flex-end; }
#{{ $wrapperId }} .dynamic-form-submit-area.align-full,   #form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-area.align-full   { justify-content: stretch; }

#{{ $wrapperId }} .dynamic-form-submit-button,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: {{ $btnRadius }}px !important;
    background: {{ $btnBg }} !important;
    color: {{ $btnTextCol }} !important;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    transition:
        background-color .2s ease,
        transform .2s ease,
        opacity .2s ease;
}

#{{ $wrapperId }} .dynamic-form-submit-button:hover,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-button:hover {
    background: {{ $btnHoverBg }} !important;
    color: {{ $btnHoverText }} !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

#{{ $wrapperId }} .dynamic-form-submit-button:active,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-button:active {
    transform: translateY(1px);
}

#{{ $wrapperId }} .dynamic-form-submit-button:disabled,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-button:disabled {
    opacity: .65;
    cursor: not-allowed;
}

#{{ $wrapperId }} .btn-w-100,
#form-wrapper-{{ $formInstanceId }} .btn-w-100 {
    width: 100% !important;
}

/* =========================================================
   MESSAGES & COUPON STYLES
========================================================= */
#{{ $wrapperId }} .dynamic-form-ajax-message,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-ajax-message {
    margin-bottom: 20px;
}

#{{ $wrapperId }} .dynamic-form-alert,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-alert {
    padding: 15px 17px;
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.6;
}

#{{ $wrapperId }} .dynamic-form-alert-error,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-alert-error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

#{{ $wrapperId }} .dynamic-form-alert-error ul,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-alert-error ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

#{{ $wrapperId }} .dynamic-form-alert-error li,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-alert-error li {
    margin-bottom: 3px;
}

#{{ $wrapperId }} .dynamic-form-success,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-success {
    padding: 45px 25px;
    margin: 10px 0;
    border: 1px solid #bbf7d0;
    border-radius: 14px;
    background: #f0fdf4;
    text-align: center;
}

#{{ $wrapperId }} .dynamic-form-success-icon,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-success-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #16a34a;
    color: #fff;
    font-size: 38px;
    font-weight: 700;
    line-height: 1;
}

#{{ $wrapperId }} .dynamic-form-success h3,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-success h3 {
    margin: 0 0 10px;
    color: #166534;
    font-size: 24px;
    line-height: 1.3;
}

#{{ $wrapperId }} .dynamic-form-success-text,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-success-text {
    max-width: 600px;
    margin: 0 auto 22px;
    color: #4b5563;
    font-size: 15px;
    line-height: 1.7;
}

/* Coupon Reveal Card Styles */
#{{ $wrapperId }} .dynamic-form-coupon-reveal,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-reveal {
    margin: 18px auto 22px;
    max-width: 380px;
}

#{{ $wrapperId }} .dynamic-form-coupon-card,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-card {
    background: #ffffff;
    border: 2px dashed #16a34a;
    border-radius: 10px;
    padding: 16px 18px;
    box-shadow: 0 4px 14px rgba(22, 163, 74, 0.08);
    text-align: center;
}

#{{ $wrapperId }} .dynamic-form-coupon-badge,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-badge {
    display: inline-block;
    background: #dcfce7;
    color: #15803d;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 10px;
}

#{{ $wrapperId }} .dynamic-form-coupon-code-row,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-code-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 8px;
}

#{{ $wrapperId }} .dynamic-form-coupon-code,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-code {
    font-family: monospace, sans-serif;
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 1.5px;
    background: #f1f5f9;
    padding: 6px 14px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    user-select: all;
}

#{{ $wrapperId }} .dynamic-form-coupon-copy,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-copy {
    border: 0;
    background: #16a34a;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
}

#{{ $wrapperId }} .dynamic-form-coupon-copy:hover,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-copy:hover {
    background: #15803d;
    transform: translateY(-1px);
}

#{{ $wrapperId }} .dynamic-form-coupon-copy.copied,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-copy.copied {
    background: #047857 !important;
}

#{{ $wrapperId }} .dynamic-form-coupon-hint,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-coupon-hint {
    display: block;
    font-size: 11.5px;
    color: #64748b;
    margin-top: 4px;
}

#{{ $wrapperId }} .dynamic-form-success-again,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-success-again {
    min-height: 44px;
    padding: 10px 22px;
    border: 0;
    border-radius: 7px;
    background: #16a34a;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

#{{ $wrapperId }} .dynamic-form-success-again:hover,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-success-again:hover {
    opacity: .9;
}

#{{ $wrapperId }} .dynamic-form-no-fields,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-no-fields {
    grid-column: span 12;
    padding: 20px;
    border: 1px dashed #d1d5db;
    border-radius: 6px;
    text-align: center;
    color: #6b7280;
}

#{{ $wrapperId }} .dynamic-form-html,
#form-wrapper-{{ $formInstanceId }} .dynamic-form-html {
    width: 100%;
}

@media (max-width: 768px) {

    #{{ $wrapperId }}.df-flow-inline .dynamic-form-grid,
    #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-grid {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }

    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-1,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-1,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-2,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-2,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-3,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-3,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-4,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-4,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-5,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-5,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-6,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-6,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-7,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-7,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-8,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-8,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-9,  #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-9,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-10, #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-10,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-11, #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-11,
    #{{ $wrapperId }}.df-flow-inline .dynamic-form-column-12, #form-wrapper-{{ $formInstanceId }}.df-flow-inline .dynamic-form-column-12 {
        grid-column: span 1 !important;
    }

    #{{ $wrapperId }} .dynamic-form-submit-inline,
    #form-wrapper-{{ $formInstanceId }} .dynamic-form-submit-inline {
        grid-column: span 1 !important;
    }

    #{{ $wrapperId }} .dynamic-form-title,
    #form-wrapper-{{ $formInstanceId }} .dynamic-form-title {
        font-size: 24px;
    }

    #{{ $wrapperId }} .dynamic-form-success,
    #form-wrapper-{{ $formInstanceId }} .dynamic-form-success {
        padding: 35px 18px;
    }

    #{{ $wrapperId }} .dynamic-form-success h3,
    #form-wrapper-{{ $formInstanceId }} .dynamic-form-success h3 {
        font-size: 21px;
    }

    #{{ $wrapperId }} .dynamic-form-success-icon,
    #form-wrapper-{{ $formInstanceId }} .dynamic-form-success-icon {
        width: 60px;
        height: 60px;
        font-size: 32px;
    }

    #{{ $wrapperId }},
    #form-wrapper-{{ $formInstanceId }} {
        padding: 16px !important;
    }

}

{!! $form->custom_css ?? '' !!}

</style>


<script>

(function () {

    const form = document.getElementById(
        @json($formInstanceId)
    );

    if (!form) {
        return;
    }


    const wrapper =
        form.closest('.dynamic-form-wrapper');

    if (!wrapper) {
        return;
    }


    const messageBox =
        wrapper.querySelector(
            '.dynamic-form-ajax-message'
        );

    const successBox =
        wrapper.querySelector(
            '.dynamic-form-success'
        );

    const successText =
        wrapper.querySelector(
            '.dynamic-form-success-text'
        );

    const successAgain =
        wrapper.querySelector(
            '.dynamic-form-success-again'
        );

    const submitButton =
        form.querySelector(
            '.dynamic-form-submit-button'
        );

    const submitText =
        form.querySelector(
            '.dynamic-form-submit-text'
        );

    const loadingText =
        form.querySelector(
            '.dynamic-form-submit-loading'
        );

    const couponWrap =
        wrapper.querySelector(
            '.dynamic-form-coupon-reveal'
        );

    const couponCodeEl =
        wrapper.querySelector(
            '.dynamic-form-coupon-code'
        );

    const couponCopyBtn =
        wrapper.querySelector(
            '.dynamic-form-coupon-copy'
        );


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Errors
    |--------------------------------------------------------------------------
    */

    function clearErrors() {

        form
            .querySelectorAll(
                '.dynamic-form-js-error'
            )
            .forEach(
                element => element.remove()
            );

        form
            .querySelectorAll(
                '.is-invalid'
            )
            .forEach(
                element =>
                    element.classList.remove(
                        'is-invalid'
                    )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Validation Errors
    |--------------------------------------------------------------------------
    */

    function showValidationErrors(
        errors
    ) {

        clearErrors();

        const errorList = [];

        Object.keys(errors || {})
            .forEach(
                fieldName => {

                    const fieldErrors =
                        Array.isArray(
                            errors[fieldName]
                        )
                            ? errors[fieldName]
                            : [
                                errors[fieldName]
                            ];


                    fieldErrors.forEach(
                        error => {

                            errorList.push(
                                error
                            );
                        }
                    );


                    let field =
                        form.querySelector(
                            '[name="' +
                            CSS.escape(
                                fieldName
                            ) +
                            '"]'
                        );


                    if (!field) {

                        field =
                            form.querySelector(
                                '[name="' +
                                CSS.escape(
                                    fieldName
                                ) +
                                '[]"]'
                            );
                    }


                    if (field) {

                        field.classList.add(
                            'is-invalid'
                        );


                        const fieldWrapper =
                            field.closest(
                                '.dynamic-form-field'
                            );


                        if (
                            fieldWrapper &&
                            fieldErrors.length
                        ) {

                            const errorElement =
                                document.createElement(
                                    'div'
                                );

                            errorElement.className =
                                'dynamic-form-field-error dynamic-form-js-error';

                            errorElement.textContent =
                                fieldErrors[0];

                            fieldWrapper.appendChild(
                                errorElement
                            );
                        }
                    }

                }
            );


        let html =
            '<div class="dynamic-form-alert dynamic-form-alert-error">';

        html +=
            '<strong>Please correct the errors below.</strong>';


        if (errorList.length) {

            html += '<ul>';

            errorList.forEach(
                error => {

                    html +=
                        '<li>' +
                        escapeHtml(error) +
                        '</li>';
                }
            );

            html += '</ul>';
        }


        html += '</div>';


        messageBox.innerHTML =
            html;


        const firstInvalid =
            form.querySelector(
                '.is-invalid'
            );


        if (firstInvalid) {

            firstInvalid.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            setTimeout(
                () => firstInvalid.focus(),
                300
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();


            clearErrors();


            messageBox.innerHTML =
                '';


            if (
                !form.checkValidity()
            ) {

                form.reportValidity();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Loading
            |--------------------------------------------------------------------------
            */

            if (submitButton) {

                submitButton.disabled =
                    true;
            }


            if (submitText) {

                submitText.style.display =
                    'none';
            }


            if (loadingText) {

                loadingText.style.display =
                    'inline';
            }


            try {

                const formData =
                    new FormData(form);


                const response =
                    await fetch(
                        form.action,
                        {
                            method: 'POST',

                            body: formData,

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json'
                            },

                            credentials:
                                'same-origin'
                        }
                    );


                let data;


                try {

                    data =
                        await response.json();

                } catch (jsonError) {

                    throw new Error(
                        'Invalid server response.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Validation Error
                |--------------------------------------------------------------------------
                */

                if (
                    response.status === 422 &&
                    data.errors
                ) {

                    showValidationErrors(
                        data.errors
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Rate Limit
                |--------------------------------------------------------------------------
                */

                if (
                    response.status === 429
                ) {

                    messageBox.innerHTML =
                        '<div class="dynamic-form-alert dynamic-form-alert-error">' +
                        '<strong>Too Many Requests</strong><br>' +
                        escapeHtml(
                            data.message ||
                            'Please try again later.'
                        ) +
                        '</div>';

                    messageBox.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Other Server Error
                |--------------------------------------------------------------------------
                */

                if (
                    !response.ok ||
                    !data.success
                ) {

                    messageBox.innerHTML =
                        '<div class="dynamic-form-alert dynamic-form-alert-error">' +
                        escapeHtml(
                            data.message ||
                            'Something went wrong. Please try again later.'
                        ) +
                        '</div>';

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */

                if (data.success) {

                    successText.textContent =
                        data.message ||
                        'Your message has been sent successfully.';

                    // Reveal Coupon Code if returned by lead sync
                    if (data.coupon_code && couponWrap && couponCodeEl) {
                        couponCodeEl.textContent = data.coupon_code;
                        couponWrap.style.display = 'block';

                        if (couponCopyBtn) {
                            couponCopyBtn.onclick = function() {
                                const copyCode = data.coupon_code;
                                if (navigator.clipboard && window.isSecureContext) {
                                    navigator.clipboard.writeText(copyCode).then(() => {
                                        markCopied();
                                    }).catch(() => fallbackCopy(copyCode));
                                } else {
                                    fallbackCopy(copyCode);
                                }
                            };
                        }
                    } else if (couponWrap) {
                        couponWrap.style.display = 'none';
                    }

                    function markCopied() {
                        if (!couponCopyBtn) return;
                        const originalHtml = couponCopyBtn.innerHTML;
                        couponCopyBtn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                        couponCopyBtn.classList.add('copied');
                        setTimeout(() => {
                            couponCopyBtn.innerHTML = originalHtml;
                            couponCopyBtn.classList.remove('copied');
                        }, 2500);
                    }

                    function fallbackCopy(text) {
                        const tempInput = document.createElement('input');
                        tempInput.value = text;
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        try {
                            document.execCommand('copy');
                            markCopied();
                        } catch (err) {}
                        document.body.removeChild(tempInput);
                    }


                    form.style.display =
                        'none';


                    messageBox.innerHTML =
                        '';


                    successBox.style.display =
                        'block';


                    successBox.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Redirect
                    |--------------------------------------------------------------------------
                    */

                    if (
                        data.redirect_url
                    ) {

                        setTimeout(
                            function () {

                                window.location.href =
                                    data.redirect_url;

                            },
                            1200
                        );
                    }
                }

            } catch (error) {

                messageBox.innerHTML =
                    '<div class="dynamic-form-alert dynamic-form-alert-error">' +
                    'Unable to submit the form right now. ' +
                    'Please check your internet connection and try again.' +
                    '</div>';

            } finally {

                if (submitButton) {

                    submitButton.disabled =
                        false;
                }


                if (submitText) {

                    submitText.style.display =
                        'inline';
                }


                if (loadingText) {

                    loadingText.style.display =
                        'none';
                }
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Send Another Message
    |--------------------------------------------------------------------------
    */

    if (successAgain) {

        successAgain.addEventListener(
            'click',
            function () {

                if (couponWrap) {
                    couponWrap.style.display = 'none';
                }

                successBox.style.display =
                    'none';

                form.style.display =
                    '';


                form.reset();


                clearErrors();


                messageBox.innerHTML =
                    '';


                const firstInput =
                    form.querySelector(
                        'input:not([type="hidden"]), textarea, select'
                    );


                if (firstInput) {

                    firstInput.focus();
                }

            }
        );
    }

})();

</script>