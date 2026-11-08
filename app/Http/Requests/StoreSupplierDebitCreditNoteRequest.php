<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierDebitCreditNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Target Supplier & Contextual References
            'supplier_id'          => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_id'          => ['nullable', 'integer', 'exists:purchases,id'],
            'purchase_return_id'   => ['nullable', 'integer', 'exists:purchase_returns,id'],
            'supplier_exchange_id' => ['nullable', 'integer', 'exists:supplier_exchanges,id'],

            // Note Classification
            'note_type'            => ['required', 'string', 'in:debit_note,credit_note'],
            'reason_type'          => [
                'required',
                'string',
                'in:price_difference,post_purchase_discount,damaged_goods,tax_adjustment,shortage,exchange_difference,rebate,general_adjustment',
            ],
            'note_date'            => ['required', 'date'],

            // Financial Valuations
            'amount'               => ['required', 'numeric', 'gt:0'],
            'tax_amount'           => ['nullable', 'numeric', 'min:0'],

            // Documentation & Processing Settings
            'description'          => ['nullable', 'string', 'max:1000'],
            'attachment_file'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'auto_approve'         => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom attribute labels for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'supplier_id'          => 'Supplier',
            'purchase_id'          => 'Purchase Invoice Reference',
            'purchase_return_id'   => 'Purchase Return Reference',
            'supplier_exchange_id' => 'Supplier Exchange Reference',
            'note_type'            => 'Note Type',
            'reason_type'          => 'Adjustment Reason',
            'note_date'            => 'Note Date',
            'amount'               => 'Base Adjustment Amount',
            'tax_amount'           => 'Tax Amount',
            'description'          => 'Description / Remarks',
            'attachment_file'      => 'Attachment Document',
        ];
    }
}