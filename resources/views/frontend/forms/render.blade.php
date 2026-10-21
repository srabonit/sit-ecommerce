@php
    $formInstanceId = 'dynamic-form-' . $form->id . '-' . uniqid();

    // Universal Helper for resolving form assets or default files
    $resolveFormAsset = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };
@endphp

<div
    class="dynamic-form-wrapper"
    id="form-wrapper-{{ $formInstanceId }}"
>

    {{-- =========================================================
        FORM HEADER
    ========================================================== --}}

    @if($form->name || $form->description)

        <div class="dynamic-form-header">

            @if($form->name)

                <h2 class="dynamic-form-title">
                    {{ $form->name }}
                </h2>

            @endif


            @if($form->description)

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
        SUCCESS MESSAGE
    ========================================================== --}}

    <div
        class="dynamic-form-success"
        id="form-success-{{ $formInstanceId }}"
        style="display:none;"
    >

        <div class="dynamic-form-success-icon">
            ✓
        </div>

        <h3>
            Message Sent Successfully!
        </h3>

        <p
            class="dynamic-form-success-text"
            id="form-success-text-{{ $formInstanceId }}"
        ></p>

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
            FIELDS
        ====================================================== --}}

        <div class="dynamic-form-grid">

            @forelse($form->fields as $field)

                @php

                    $fieldName =
                        trim((string) $field->name);

                    $fieldId =
                        'form-field-' .
                        $field->id .
                        '-' .
                        uniqid();

                    $columnWidth =
                        (int) (
                            $field->column_width ?: 12
                        );

                    if ($columnWidth < 1) {
                        $columnWidth = 12;
                    }

                    if ($columnWidth > 12) {
                        $columnWidth = 12;
                    }

                    $oldValue =
                        old(
                            $fieldName,
                            $field->default_value
                        );

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

                    {{-- LABEL --}}

                    @if(
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
                            placeholder="{{ $field->placeholder }}"
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
                            placeholder="{{ $field->placeholder }}"
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
                            placeholder="{{ $field->placeholder }}"
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
                            placeholder="{{ $field->placeholder }}"
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
                            placeholder="{{ $field->placeholder }}"
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
                                {{ $field->placeholder ?: 'Select an option' }}
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
                            placeholder="{{ $field->placeholder }}"
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
                            placeholder="{{ $field->placeholder }}"
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
                        SERVER ERROR
                    ================================================== --}}

                    @error($fieldName)

                        <div class="dynamic-form-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            @empty

                <div class="dynamic-form-no-fields">

                    No fields have been added
                    to this form yet.

                </div>

            @endforelse

        </div>


        {{-- =========================================================
            SUBMIT
        ========================================================== --}}

        @if(
            $form->fields
                ->where('type', '!=', 'html')
                ->count() > 0
        )

            <div class="dynamic-form-submit-area">

                <button
                    type="submit"
                    class="dynamic-form-submit-button"
                >

                    <span class="dynamic-form-submit-text">
                        Submit
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


<style>

.dynamic-form-wrapper {
    width: 100%;
    box-sizing: border-box;
}

.dynamic-form-header {
    margin-bottom: 25px;
}

.dynamic-form-title {
    margin: 0 0 8px;
    font-size: 28px;
    line-height: 1.3;
    font-weight: 700;
    color: #111827;
}

.dynamic-form-description {
    font-size: 15px;
    line-height: 1.7;
    color: #6b7280;
}

.dynamic-form-grid {
    display: grid;
    grid-template-columns:
        repeat(12, minmax(0, 1fr));
    gap: 20px;
}

.dynamic-form-field {
    min-width: 0;
}

.dynamic-form-column-1 {
    grid-column: span 1;
}

.dynamic-form-column-2 {
    grid-column: span 2;
}

.dynamic-form-column-3 {
    grid-column: span 3;
}

.dynamic-form-column-4 {
    grid-column: span 4;
}

.dynamic-form-column-5 {
    grid-column: span 5;
}

.dynamic-form-column-6 {
    grid-column: span 6;
}

.dynamic-form-column-7 {
    grid-column: span 7;
}

.dynamic-form-column-8 {
    grid-column: span 8;
}

.dynamic-form-column-9 {
    grid-column: span 9;
}

.dynamic-form-column-10 {
    grid-column: span 10;
}

.dynamic-form-column-11 {
    grid-column: span 11;
}

.dynamic-form-column-12 {
    grid-column: span 12;
}

.dynamic-form-label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    line-height: 1.4;
    font-weight: 600;
    color: #222;
}

.dynamic-form-required {
    color: #dc2626;
    margin-left: 3px;
}

.dynamic-form-control {
    display: block;
    width: 100%;
    min-height: 45px;
    padding: 10px 13px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
    color: #111827;
    font-size: 14px;
    line-height: 1.5;
    outline: none;
    box-sizing: border-box;
    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}

textarea.dynamic-form-control {
    min-height: 120px;
    resize: vertical;
}

.dynamic-form-control:focus {
    border-color: #2563eb;
    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, .10);
}

.dynamic-form-control.is-invalid {
    border-color: #dc2626 !important;
    box-shadow:
        0 0 0 3px
        rgba(220, 38, 38, .08);
}

.dynamic-form-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.dynamic-form-option {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #333;
}

.dynamic-form-option input {
    margin: 0;
}

.dynamic-form-help {
    margin-top: 6px;
    font-size: 12px;
    color: #6b7280;
}

.dynamic-form-file-info {
    margin-top: 6px;
    font-size: 12px;
    line-height: 1.6;
    color: #6b7280;
}

.dynamic-form-field-error {
    margin-top: 6px;
    font-size: 13px;
    color: #dc2626;
}

.dynamic-form-submit-area {
    margin-top: 25px;
}

.dynamic-form-submit-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 45px;
    min-width: 110px;
    padding: 10px 24px;
    border: 0;
    border-radius: 6px;
    background: #2563eb;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition:
        opacity .2s ease,
        transform .2s ease;
}

.dynamic-form-submit-button:hover {
    opacity: .9;
}

.dynamic-form-submit-button:active {
    transform: translateY(1px);
}

.dynamic-form-submit-button:disabled {
    opacity: .65;
    cursor: not-allowed;
}

.dynamic-form-ajax-message {
    margin-bottom: 20px;
}

.dynamic-form-alert {
    padding: 15px 17px;
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.6;
}

.dynamic-form-alert-error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.dynamic-form-alert-error ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

.dynamic-form-alert-error li {
    margin-bottom: 3px;
}

.dynamic-form-success {
    padding: 45px 25px;
    margin: 10px 0;
    border: 1px solid #bbf7d0;
    border-radius: 14px;
    background: #f0fdf4;
    text-align: center;
}

.dynamic-form-success-icon {
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

.dynamic-form-success h3 {
    margin: 0 0 10px;
    color: #166534;
    font-size: 24px;
    line-height: 1.3;
}

.dynamic-form-success-text {
    max-width: 600px;
    margin: 0 auto 22px;
    color: #4b5563;
    font-size: 15px;
    line-height: 1.7;
}

.dynamic-form-success-again {
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

.dynamic-form-success-again:hover {
    opacity: .9;
}

.dynamic-form-no-fields {
    grid-column: span 12;
    padding: 20px;
    border: 1px dashed #d1d5db;
    border-radius: 6px;
    text-align: center;
    color: #6b7280;
}

.dynamic-form-html {
    width: 100%;
}

@media (max-width: 768px) {

    .dynamic-form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .dynamic-form-column-1,
    .dynamic-form-column-2,
    .dynamic-form-column-3,
    .dynamic-form-column-4,
    .dynamic-form-column-5,
    .dynamic-form-column-6,
    .dynamic-form-column-7,
    .dynamic-form-column-8,
    .dynamic-form-column-9,
    .dynamic-form-column-10,
    .dynamic-form-column-11,
    .dynamic-form-column-12 {
        grid-column: span 1;
    }

    .dynamic-form-title {
        font-size: 24px;
    }

    .dynamic-form-success {
        padding: 35px 18px;
    }

    .dynamic-form-success h3 {
        font-size: 21px;
    }

    .dynamic-form-success-icon {
        width: 60px;
        height: 60px;
        font-size: 32px;
    }

}

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