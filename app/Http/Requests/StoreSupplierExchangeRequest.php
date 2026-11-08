<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierExchangeRequest extends FormRequest
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
            // Exchange Header
            'supplier_id'                     => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_id'                     => ['nullable', 'integer', 'exists:purchases,id'],
            'purchase_return_id'              => ['nullable', 'integer', 'exists:purchase_returns,id'],
            'branch_id'                       => ['nullable', 'integer', 'exists:branches,id'],
            'warehouse_id'                    => ['nullable', 'integer', 'exists:warehouses,id'],
            'exchange_date'                   => ['required', 'date'],
            'difference_settlement'           => ['required', 'in:adjust_ledger,cash_refund,cash_payment,credit_debit_note'],
            'notes'                           => ['nullable', 'string', 'max:1000'],
            'auto_approve'                    => ['nullable', 'boolean'],

            // Outward Items (Goods sent back to supplier)
            'returned_items'                  => ['required', 'array', 'min:1'],
            'returned_items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'returned_items.*.variant_id'     => ['nullable', 'integer', 'exists:product_variants,id'],
            'returned_items.*.sku'            => ['nullable', 'string', 'max:100'],
            'returned_items.*.barcode'        => ['nullable', 'string', 'max:100'],
            'returned_items.*.batch_no'       => ['nullable', 'string', 'max:100'],
            'returned_items.*.quantity'       => ['required', 'numeric', 'gt:0'],
            'returned_items.*.unit_cost'      => ['required', 'numeric', 'min:0'],
            'returned_items.*.remarks'        => ['nullable', 'string', 'max:255'],

            // Inward Items (Replacement goods received from supplier)
            'replacement_items'               => ['required', 'array', 'min:1'],
            'replacement_items.*.product_id'  => ['required', 'integer', 'exists:products,id'],
            'replacement_items.*.variant_id'  => ['nullable', 'integer', 'exists:product_variants,id'],
            'replacement_items.*.sku'         => ['nullable', 'string', 'max:100'],
            'replacement_items.*.barcode'     => ['nullable', 'string', 'max:100'],
            'replacement_items.*.batch_no'    => ['nullable', 'string', 'max:100'],
            'replacement_items.*.quantity'    => ['required', 'numeric', 'gt:0'],
            'replacement_items.*.unit_cost'   => ['required', 'numeric', 'min:0'],
            'replacement_items.*.remarks'     => ['nullable', 'string', 'max:255'],
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
            'supplier_id'                      => 'Supplier',
            'purchase_id'                      => 'Purchase Invoice Reference',
            'exchange_date'                    => 'Exchange Date',
            'difference_settlement'            => 'Valuation Difference Settlement Method',
            'returned_items'                   => 'Returned Outward Items',
            'returned_items.*.product_id'      => 'Returned Product',
            'returned_items.*.quantity'        => 'Returned Quantity',
            'returned_items.*.unit_cost'       => 'Returned Unit Valuation',
            'replacement_items'                => 'Replacement Inward Items',
            'replacement_items.*.product_id'   => 'Replacement Product',
            'replacement_items.*.quantity'     => 'Replacement Quantity',
            'replacement_items.*.unit_cost'    => 'Replacement Unit Valuation',
        ];
    }
}