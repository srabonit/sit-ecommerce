<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReturnRequest extends FormRequest
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
            // Return Note Header
            'supplier_id'           => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_id'           => ['nullable', 'integer', 'exists:purchases,id'],
            'branch_id'             => ['nullable', 'integer', 'exists:branches,id'],
            'warehouse_id'          => ['nullable', 'integer', 'exists:warehouses,id'],
            'return_date'           => ['required', 'date'],
            'return_reason'         => ['required', 'in:defective,wrong_item,damaged_in_transit,expired,excess_stock,other'],
            'settlement_type'       => ['required', 'in:bill_adjustment,refund,credit_note,replacement'],
            'supplier_reference_no' => ['nullable', 'string', 'max:100'],
            'attachment_file'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'auto_approve'          => ['nullable', 'boolean'],

            // Return Line Items
            'items'                         => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id'      => ['nullable', 'integer', 'exists:purchase_items,id'],
            'items.*.product_id'            => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'            => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.sku'                   => ['nullable', 'string', 'max:100'],
            'items.*.barcode'               => ['nullable', 'string', 'max:100'],
            'items.*.batch_no'              => ['nullable', 'string', 'max:100'],
            'items.*.return_quantity'       => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost'             => ['required', 'numeric', 'min:0'],
            'items.*.tax_amount'            => ['nullable', 'numeric', 'min:0'],
            'items.*.item_reason'           => ['nullable', 'string', 'max:255'],
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
            'supplier_id'             => 'Supplier',
            'purchase_id'             => 'Purchase Bill',
            'branch_id'               => 'Branch',
            'warehouse_id'            => 'Warehouse',
            'return_date'             => 'Return Date',
            'return_reason'           => 'Return Reason',
            'settlement_type'         => 'Settlement Type',
            'supplier_reference_no'   => 'Supplier Credit Memo / Reference',
            'attachment_file'         => 'Attachment Document',
            'items'                   => 'Return Items',
            'items.*.product_id'      => 'Product',
            'items.*.variant_id'      => 'Product Variant',
            'items.*.return_quantity' => 'Return Quantity',
            'items.*.unit_cost'       => 'Unit Cost',
        ];
    }
}