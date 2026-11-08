@forelse($templates as $template)
    @php
        $vars = is_array($template->variables)
            ? $template->variables
            : (is_string($template->variables) ? (json_decode($template->variables, true) ?? array_filter(array_map('trim', explode(',', $template->variables)))) : []);
    @endphp

    <tr class="sms-template-row">

        {{-- Template Name & Identifier Key --}}
        <td class="ps-3 py-3">
            <div class="sms-template-name">
                {{ $template->name }}
            </div>

            <div class="sms-template-key">
                <span>{{ $template->key }}</span>

                @if(!empty($template->is_system))
                    <span class="sms-system-lock"
                          title="System Locked Template">
                        <i class="fas fa-lock"></i>
                    </span>
                @endif
            </div>
        </td>

        {{-- Message Preview --}}
        <td>
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary sms-preview-btn btn-preview"
                data-id="{{ $template->id }}"
            >
                <i class="fas fa-eye"></i>
                <span>View Message</span>
            </button>

            {{-- Hidden Template Data --}}
            <div id="tpl-data-{{ $template->id }}"
                 class="d-none"
                 data-name="{{ $template->name }}"
                 data-key="{{ $template->key }}">

                <div class="tpl-body-content">{{ $template->body }}</div>
                <div class="tpl-vars-content">@json($vars)</div>

            </div>
        </td>

        {{-- Format & Parts --}}
        <td>
            <div class="sms-format-info">
                <span class="sms-parts-count">
                    {{ $template->estimated_parts ?? 1 }} Part
                </span>

                <span class="sms-format-divider">•</span>

                <span class="sms-encoding-type">
                    {{ $template->is_unicode ? 'Unicode' : 'GSM-7' }}
                </span>
            </div>
        </td>

        {{-- Status Toggle --}}
        <td>
            <div class="form-check form-switch sms-status-switch">
                <input
                    class="form-check-input status-toggle"
                    type="checkbox"
                    role="switch"
                    data-id="{{ $template->id }}"
                    data-url="{{ route('admin.sms.templates.toggle_status', $template->id) }}"
                    {{ $template->is_active ? 'checked' : '' }}
                    aria-label="Toggle template status"
                >
            </div>
        </td>

        {{-- Actions --}}
        <td class="pe-3 text-end">
            <div class="sms-action-group">

                <a href="{{ route('admin.sms.templates.edit', $template->id) }}"
                   class="btn btn-outline-secondary btn-sm sms-action-btn"
                   title="Edit Template"
                   aria-label="Edit Template">
                    <i class="fas fa-pen"></i>
                </a>

                @if(!$template->is_system)
                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm sms-action-btn sms-delete-action btn-delete-confirm"
                        data-name="{{ $template->name }}"
                        data-action="{{ route('admin.sms.templates.destroy', $template->id) }}"
                        title="Delete Template"
                        aria-label="Delete Template"
                    >
                        <i class="fas fa-trash-alt"></i>
                    </button>
                @endif

            </div>
        </td>

    </tr>

@empty
    <tr>
        <td colspan="5" class="sms-empty-cell">
            <div class="sms-empty-state">
                <div class="sms-empty-icon">
                    <i class="fas fa-comment-slash"></i>
                </div>

                <h6>No SMS Templates Found</h6>

                <p class="mb-0">
                    No templates match your current search or filter.
                </p>
            </div>
        </td>
    </tr>
@endforelse

<style>
/* =========================================================
   SMS TEMPLATE TABLE ROWS
   Kaiadmin Lite v1.2.0 Classic / White Classic
========================================================= */

/* Template Name */
.sms-templates-page .sms-template-name {
    color: #2a2f5b;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.sms-templates-page .sms-template-key {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 4px;
    color: #8d9498;
    font-family: monospace;
    font-size: 11px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.sms-templates-page .sms-system-lock {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 17px;
    height: 17px;
    border-radius: 4px;
    background: #f1f3f5;
    color: #8d9498;
    font-size: 9px;
}

/* Preview Button */
.sms-templates-page .sms-preview-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 30px;
    padding: 5px 10px;
    border: 1px solid #e4e7ed;
    border-radius: 5px;
    background: #f8f9fc;
    color: #59616a;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.18s ease;
}

.sms-templates-page .sms-preview-btn i {
    color: #1572e8;
    font-size: 11px;
}

.sms-templates-page .sms-preview-btn:hover {
    border-color: #cbdcff;
    background: #edf4ff;
    color: #1572e8;
}

/* Format & Parts */
.sms-templates-page .sms-format-info {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    line-height: 1.5;
}

.sms-templates-page .sms-parts-count {
    color: #424957;
    font-size: 12px;
    font-weight: 600;
}

.sms-templates-page .sms-format-divider {
    color: #c3c8ce;
    font-size: 12px;
}

.sms-templates-page .sms-encoding-type {
    color: #8d9498;
    font-size: 11px;
    font-weight: 500;
}

/* Status Switch */
.sms-templates-page .sms-status-switch {
    display: flex;
    align-items: center;
    min-height: 22px;
    padding-left: 0;
    margin: 0;
}

.sms-templates-page .sms-status-switch .form-check-input {
    float: none;
    width: 34px;
    height: 18px;
    margin: 0;
    border-color: #d6dbe1;
    background-color: #e9ecef;
    cursor: pointer;
    box-shadow: none;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

.sms-templates-page .sms-status-switch .form-check-input:checked {
    border-color: #1572e8;
    background-color: #1572e8;
}

.sms-templates-page .sms-status-switch .form-check-input:focus {
    border-color: #1572e8;
    box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.12);
}

/* Action Buttons */
.sms-templates-page .sms-action-group {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.sms-templates-page .sms-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    padding: 0;
    border: 1px solid #e6e8ed;
    border-radius: 5px;
    background: #fff;
    color: #59616a;
    font-size: 11px;
    transition: all 0.18s ease;
}

.sms-templates-page .sms-action-btn:hover {
    border-color: #cbdcff;
    background: #edf4ff;
    color: #1572e8;
}

.sms-templates-page .sms-action-btn i {
    font-size: 11px;
}

.sms-templates-page .sms-delete-action:hover {
    border-color: #f5c6cb;
    background: #fff1f2;
    color: #dc3545;
}

.sms-templates-page .sms-delete-action i {
    color: #dc3545;
}

/* Empty State */
.sms-templates-page .sms-empty-cell {
    padding: 0 !important;
    border-bottom: 0 !important;
}

.sms-templates-page .sms-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    text-align: center;
}

.sms-templates-page .sms-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 55px;
    height: 55px;
    margin-bottom: 14px;
    border: 1px solid #e9edf4;
    border-radius: 12px;
    background: #f8f9fc;
    color: #a4abb4;
    font-size: 22px;
}

.sms-templates-page .sms-empty-state h6 {
    margin-bottom: 6px;
    color: #424957;
    font-size: 14px;
    font-weight: 700;
}

.sms-templates-page .sms-empty-state p {
    max-width: 320px;
    color: #9ba1a8;
    font-size: 12px;
    line-height: 1.6;
}

/* Small Screen Adjustments */
@media (max-width: 767.98px) {
    .sms-templates-page .sms-template-name {
        font-size: 12px;
    }

    .sms-templates-page .sms-template-key {
        font-size: 10px;
    }

    .sms-templates-page .sms-empty-state {
        padding: 38px 15px;
    }
}

@media (max-width: 360px) {
    .sms-templates-page .sms-preview-btn {
        gap: 5px;
        padding: 5px 7px;
        font-size: 10px;
    }

    .sms-templates-page .sms-action-group {
        gap: 4px;
    }

    .sms-templates-page .sms-action-btn {
        width: 28px;
        height: 28px;
    }
}
</style>